<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use ninja\mailers\ContactMailer as Mailer;

class SendRecurringInvoices extends Command {

	protected $name = 'ipx:crear-facturas';
	protected $description = 'Enviar facturas recurrentes';
	protected $mailer;

	public function __construct(Mailer $mailer)
	{
		parent::__construct();
		$this->mailer = $mailer;
	}

	public function fire()
	{
		$this->info(date('Y-m-d') . ' Cargando SendRecurringInvoices...');
		$today = new DateTime();			
		
		$invoices = Invoice::with('account.timezone', 'invoice_items', 'client')
			->whereRaw('is_deleted IS FALSE AND deleted_at IS NULL AND is_recurring IS TRUE AND start_date <= ? AND (end_date IS NULL OR end_date >= ?)', array($today, $today))->get();
		$this->info(count($invoices) . ' facturas recurrentes encontradas');

		foreach ($invoices as $recurInvoice)
		{
			if ($recurInvoice->client->deleted_at)
			{
				continue;
			}

			if (!$recurInvoice->user->confirmed) {
                continue;
            }

			date_default_timezone_set($recurInvoice->account->getTimezone());			
			
			$this->info('Procesando la factura con el id ' . $recurInvoice->id . ($recurInvoice->shouldSendToday() ? ' si se enviará' : ' no se enviará'));
			
			// if (!$recurInvoice->shouldSendToday())
			// {
			// 	continue;
			// }

			$branch_id = $recurInvoice->branch_id;
	        $branch = DB::table('branches')->where('id',$branch_id)->first();
			
			$account_id = $recurInvoice->account_id;
	        $account = DB::table('accounts')->where('id',$account_id)->first();

			if(strtotime($branch->deadline) < strtotime('now'))
			{
				continue;
			}
			
			$invoice = Invoice::createNew($recurInvoice);

			$invoice->client_id = $recurInvoice->client_id;
			$invoice->recurring_invoice_id = $recurInvoice->id;

	        $invoice->branch_id = $recurInvoice->branch_id;
	        $invoiceNumber = $branch->invoice_number_counter;
			$invoice->invoice_number = $branch->invoice_number_counter;

			$invoice->amount = $recurInvoice->amount;
			$invoice->subtotal = $recurInvoice->subtotal;
			$invoice->balance = $recurInvoice->amount;
			$invoice->invoice_date = date_create()->format('Y-m-d');
			$invoice->discount = $recurInvoice->discount;
			$invoice->po_number = $recurInvoice->po_number;
			$invoice->public_notes = $recurInvoice->public_notes;
			$invoice->terms = $recurInvoice->terms;
			// $invoice->tax_name = $recurInvoice->tax_name;
			// $invoice->tax_rate = $recurInvoice->tax_rate;
			$invoice->invoice_design_id = $recurInvoice->invoice_design_id;



			$invoice->account_name=$recurInvoice->account_name;
			$invoice->account_nit=$recurInvoice->account_nit;

			$invoice->branch_name=$branch->name;
			$invoice->address1=$branch->address1;
			$invoice->address2=$branch->address2;

			$invoice->phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;

			$invoice->number_autho=$branch->number_autho;
			$invoice->deadline=$branch->deadline;
			$invoice->key_dosage=$branch->key_dosage;

			$invoice->client_nit = $recurInvoice->client_nit;
			$invoice->client_name = $recurInvoice->client_name;

			$invoice->activity_pri = $branch->activity_pri;
			$invoice->activity_sec1 = $branch->activity_sec1;
			$invoice->law = $branch->law;

			$invoice_dateCC = date("Ymd", strtotime($invoice->invoice_date));
			$invoice_date_limitCC = date("d/m/Y", strtotime($branch->deadline));

			require_once(app_path().'/includes/control_code.php');
			$cod_control = codigoControl($invoice->invoice_number, $invoice->client_nit, $invoice_dateCC, $invoice->amount, $branch->number_autho, $branch->key_dosage);

			$invoice->control_code = $cod_control;

			$invoice_date = date("d/m/Y", strtotime($invoice->invoice_date));
			require_once(app_path().'/includes/BarcodeQR.php');

		    // $ice = $invoice->amount-$invoice->fiscal;
		    $desc = $invoice->subtotal-$invoice->amount;

		    $subtotal = number_format($invoice->subtotal, 2, '.', '');
		    $amount = number_format($invoice->amount, 2, '.', '');
		    $fiscal = number_format($invoice->fiscal, 2, '.', '');

		    // $icef = number_format($ice, 2, '.', '');
		    $descf = number_format($desc, 2, '.', '');

		    // if($icef=="0.00"){
		    //   $icef = 0;
		    // }
		    if($descf=="0.00"){
		      $descf = 0;
		    }

		    $icef = 0;

		    $qr = new BarcodeQR();
		    $datosqr = $invoice->account_nit.'|'.$invoice->invoice_number.'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$subtotal.'|'.$amount.'|'.$invoice->control_code.'|'.$invoice->client_nit.'|'.$icef.'|0|0|'.$descf;
		    $qr->text($datosqr); 
		    $qr->draw(150, 'public/qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png');
		    $input_file = 'public/qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png';
		    $output_file = 'public/qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.jpg';

		    $inputqr = imagecreatefrompng($input_file);
		    list($width, $height) = getimagesize($input_file);
		    $output = imagecreatetruecolor($width, $height);
		    $white = imagecolorallocate($output,  255, 255, 255);
		    imagefilledrectangle($output, 0, 0, $width, $height, $white);
		    imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
		    imagejpeg($output, $output_file);

		    $invoice->qr=HTML::image_data('qr/' . $account->account_key .'_'. $branch->name .'_'. $invoice->invoice_number . '.jpg');


			if ($invoice->client->payment_terms)
			{
				$invoice->due_date = date_create()->modify($invoice->client->payment_terms . ' day')->format('Y-m-d');
			}
			
			$invoice->save();
				
			foreach ($recurInvoice->invoice_items as $recurItem)
			{
				$item = InvoiceItem::createNew($recurItem);
				$item->product_id = $recurItem->product_id;
				$item->qty = $recurItem->qty;
				$item->cost = $recurItem->cost;
				$item->notes = Utils::processVariables($recurItem->notes);
				$item->product_key = Utils::processVariables($recurItem->product_key);				
				$item->tax_name = $recurItem->tax_name;
				$item->tax_rate = $recurItem->tax_rate;
				$invoice->invoice_items()->save($item);
			}

			foreach ($recurInvoice->invitations as $recurInvitation)
			{
				$invitation = Invitation::createNew($recurInvitation);
				$invitation->contact_id = $recurInvitation->contact_id;
				$invitation->invitation_key = str_random(RANDOM_KEY_LENGTH);
				$invoice->invitations()->save($invitation);
			}

			$this->mailer->sendInvoice($invoice);

			$recurInvoice->last_sent_date = Carbon::now()->toDateTimeString();
			$recurInvoice->save();			
		}		

		$this->info('Done');
	}

	protected function getArguments()
	{
		return array(
			//array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}