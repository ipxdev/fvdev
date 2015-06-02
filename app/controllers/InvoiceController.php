<?php

use ninja\mailers\ContactMailer as Mailer;
use ninja\repositories\InvoiceRepository;
use ninja\repositories\ClientRepository;
use ninja\repositories\TaxRateRepository;

class InvoiceController extends \BaseController {

	protected $mailer;
	protected $invoiceRepo;
	protected $clientRepo;
	protected $taxRateRepo;

	public function __construct(Mailer $mailer, InvoiceRepository $invoiceRepo, ClientRepository $clientRepo, TaxRateRepository $taxRateRepo)
	{
		parent::__construct();

		$this->mailer = $mailer;
		$this->invoiceRepo = $invoiceRepo;
		$this->clientRepo = $clientRepo;
		$this->taxRateRepo = $taxRateRepo;
	}	

	public function index()
	{
		$data = [
			'title' => trans('texts.invoices'),
			'entityType'=>ENTITY_INVOICE, 
			'columns'=>Utils::trans(['checkbox',  'invoice_num', 'name_client', 'invoice_date','branch', 'invoice_total', 'balance_due', 'due_date', 'status', 'action'])
		];

		if (Invoice::scope()->where('is_recurring', '=', true)->count() > 0)
		{
			$data['secEntityType'] = ENTITY_RECURRING_INVOICE;
			$data['secColumns'] = Utils::trans(['checkbox', 'frequency', 'name_client', 'start_date', 'end_date', 'invoice_total', 'action']);
		}

		return View::make('list', $data);
	}

	public function getDatatable($clientPublicId = null)
  {
  	$accountId = Auth::user()->account_id;
  	$search = Input::get('sSearch');

  	return $this->invoiceRepo->getDatatable($accountId, $clientPublicId, ENTITY_INVOICE, $search);
  }

	public function getRecurringDatatable($clientPublicId = null)
    {
    	$query = $this->invoiceRepo->getRecurringInvoices(Auth::user()->account_id, $clientPublicId, Input::get('sSearch'));
    	$table = Datatable::query($query);			

    	if (!$clientPublicId) {
    		$table->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; });
    	}
    	
    	$table->addColumn('frequency', function($model) { return link_to('invoices/' . $model->public_id, $model->frequency); });

    	if (!$clientPublicId) {
    		$table->addColumn('client_name', function($model) { return link_to('clients/' . $model->client_public_id, Utils::getClientDisplayName($model)); });
    	}
    	
    	return $table->addColumn('start_date', function($model) { return Utils::fromSqlDate($model->start_date); })
    	    ->addColumn('end_date', function($model) { return Utils::fromSqlDate($model->end_date); })    	    
    	    ->addColumn('amount', function($model) { return Utils::formatMoney($model->amount, $model->currency_id); })
    	    ->addColumn('dropdown', function($model) 
    	    { 
    	    	return '<div class="btn-group tr-action" style="visibility:hidden;">
  							<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
    						'.trans('texts.select').' <span class="caret"></span>
  							</button>
  							<ul class="dropdown-menu" role="menu">
						    <li><a href="' . URL::to('invoices/'.$model->public_id.'/edit') . '">'.trans('texts.edit_invoice').'</a></li>
						    <li class="divider"></li>
						    <li><a href="javascript:archiveEntity(' . $model->public_id . ')">'.trans('texts.archive_invoice').'</a></li>
						    <li><a href="javascript:deleteEntity(' . $model->public_id . ')">'.trans('texts.delete_invoice').'</a></li>						    
						  </ul>
						</div>';
    	    })    	       	    
    	    ->make();    	
    }


	public function view($invitationKey)
	{
		$invitation = Invitation::withTrashed()->where('invitation_key', '=', $invitationKey)->firstOrFail();

		$invoice = $invitation->invoice;
		
		if (!$invoice || $invoice->is_deleted) 
		{
			return View::make('invoices.deleted');
		}

		if ($invoice->is_quote && $invoice->quote_invoice_id)
		{
			$invoice = Invoice::scope($invoice->quote_invoice_id, $invoice->account_id)->firstOrFail();

			if (!$invoice || $invoice->is_deleted) 
			{
				return View::make('invoices.deleted');
			}
		}

		$invoice->load('user', 'invoice_items', 'invoice_design', 'account.country', 'client.contacts', 'client.country');

		$client = $invoice->client;
		
		if (!$client || $client->is_deleted) 
		{
			return View::make('invoices.deleted');
		}

		if (!Auth::check() || Auth::user()->account_id != $invoice->account_id)
		{
			Activity::viewInvoice($invitation);	
			Event::fire('invoice.viewed', $invoice);
		}

		$client->account->loadLocalizationSettings();		

		$invoice->invoice_date = Utils::fromSqlDate($invoice->invoice_date);
		$invoice->due_date = Utils::fromSqlDate($invoice->due_date);
		$invoice->is_pro = $client->account->isPro();		
		
		$data = array(
			'hideHeader' => true,
			'showBreadcrumbs' => false,
			'invoice' => $invoice->hidePrivateFields(),
			'invitation' => $invitation,
			'invoiceLabels' => $client->account->getInvoiceLabels(),
		);

		return View::make('invoices.view', $data);
	}

	public function edit($publicId, $clone = false)
	{
		$invoice = Invoice::scope($publicId)->withTrashed()->with('invitations', 'account.country', 'client.contacts', 'client.country', 'invoice_items')->firstOrFail();
		$entityType = $invoice->getEntityType();

  	$contactIds = DB::table('invitations')
			->join('contacts', 'contacts.id', '=','invitations.contact_id')
			->where('invitations.invoice_id', '=', $invoice->id)
			->where('invitations.account_id', '=', Auth::user()->account_id)
			->where('invitations.deleted_at', '=', null)
			->select('contacts.public_id')->lists('public_id');
		
		if ($clone)
		{
			$invoice->id = null;
			$invoice->invoice_number = Auth::user()->account->getNextInvoiceNumber($invoice->is_quote);
			$invoice->balance = $invoice->amount;
			$method = 'POST';			
			$url = "{$entityType}s";
		}
		else
		{
			Utils::trackViewed($invoice->invoice_number . ' - ' . $invoice->client->getDisplayName(), $invoice->getEntityType());
			$method = 'PUT';
			$url = "{$entityType}s/{$publicId}";
		}
		
		$invoice->invoice_date = Utils::fromSqlDate($invoice->invoice_date);
		$invoice->due_date = Utils::fromSqlDate($invoice->due_date);
		$invoice->start_date = Utils::fromSqlDate($invoice->start_date);
		$invoice->end_date = Utils::fromSqlDate($invoice->end_date);
		$invoice->is_pro = Auth::user()->isPro();
   		
   		$invoiceDesigns = InvoiceDesign::where('account_id',\Auth::user()->account_id)->orderBy('public_id', 'desc')->get();
   		// $invoiceDesigns = InvoiceDesign::where('account_id',\Auth::user()->account_id)->where('id',$invoice->invoice_design_id)->orderBy('public_id', 'desc')->first();

		$data = array(
				'entityType' => $entityType,
				'showBreadcrumbs' => $clone,
				'account' => $invoice->account,
				'invoice' => $invoice, 
				'data' => false,
				'method' => $method, 
				'invoiceDesigns' => $invoiceDesigns,
				'invitationContactIds' => $contactIds,
				'url' => $url, 
				'title' => trans("texts.edit_{$entityType}"),
				'client' => $invoice->client);
		$data = array_merge($data, self::getViewModel());		

		// Set the invitation link on the client's contacts
		$clients = $data['clients'];
		foreach ($clients as $client)
		{
			if ($client->id == $invoice->client->id)
			{
				foreach ($invoice->invitations as $invitation)
				{
					foreach ($client->contacts as $contact)
					{
						if ($invitation->contact_id == $contact->id)
						{
							$contact->invitation_link = $invitation->getLink();
						}
					}				
				}
				break;
			}
		}
	
		return View::make('invoices.edit', $data);
	}

	public function create($clientPublicId = 0)
	{	
		$client = null;
		// $invoiceNumber = Auth::user()->branch->getNextInvoiceNumber();
// 'invoiceNumber' => $invoiceNumber,
		$account = Account::with('country')->findOrFail(Auth::user()->account_id);

		if ($clientPublicId) 
		{
			$client = Client::scope($clientPublicId)->firstOrFail();
   		}
   		$invoiceDesigns = InvoiceDesign::where('account_id',\Auth::user()->account_id)->orderBy('public_id', 'desc')->get();

		$data = array(
				'entityType' => ENTITY_INVOICE,
				'account' => $account,
				'invoice' => null,
				'showBreadcrumbs' => false,
				'data' => Input::old('data'), 
				'invoiceDesigns' => $invoiceDesigns,
				'method' => 'POST', 
				'url' => 'invoices', 
				'title' => trans('texts.new_invoice'),
				'client' => $client);
		$data = array_merge($data, self::getViewModel());				

		return View::make('invoices.edit', $data);
	}

	private static function getViewModel()
	{
		return [
			'account' => Auth::user()->account,
			'branches' => Branch::where('account_id', '=', Auth::user()->account_id)->where('id',Auth::user()->branch_id)->orderBy('public_id')->get(),
			'products' => Product::scope()->orderBy('id')->get(array('product_key','notes','cost','qty')),
			'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'clients' => Client::scope()->with('contacts', 'country')->orderBy('name')->get(),
			'taxRates' => TaxRate::scope()->orderBy('name')->get(),
			'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'sizes' => Size::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
			'paymentTerms' => PaymentTerm::remember(DEFAULT_QUERY_CACHE)->orderBy('num_days')->get(['name', 'num_days']),
			'industries' => Industry::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),				
			'frequencies' => array(
				1 => 'Semanal',
				2 => 'Cada 2 semanas',
				3 => 'Cada 4 semanas',
				4 => 'Mensual',
				5 => 'Trimestral',
				6 => 'Semestral',
				7 => 'Anual'
			)
		];
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{		
		return InvoiceController::save();
	}

	private function save($publicId = null)
	{	
		$action = Input::get('action');
		$entityType = Input::get('entityType');

		if ($action == 'archive' || $action == 'delete' || $action == 'mark')
		{

			return InvoiceController::bulk($entityType);
		}

		$input = json_decode(Input::get('data'));					
		
		$invoice = $input->invoice;

		if (Utils::isAdmin())
	    {
	      $branch_id = $input->invoice->branch_id;
	      $branch = Branch::where('account_id', '=', Auth::user()->account_id)->where('public_id',$branch_id)->first();

	      // $branch = DB::table('branches')->where('id',$branch_id)->first();
	    }
	    else
	    {
	      $branch = Auth::user()->branch;
	      $branch_id = $branch->id;		
	      $branch = DB::table('branches')->where('id',$branch_id)->first();

	    }

		$today = new DateTime('now');

		$today = $today->format('Y-m-d');
		$datelimit = DateTime::createFromFormat('Y-m-d', $branch->deadline);	
		$datelimit = $datelimit->format('Y-m-d');

		$valoresPrimera = explode ("-", $datelimit); 
		$valoresSegunda = explode ("-", $today); 

		$diaPrimera    = $valoresPrimera[2];  
		$mesPrimera  = $valoresPrimera[1];  
		$anyoPrimera   = $valoresPrimera[0]; 

		$diaSegunda   = $valoresSegunda[2];  
		$mesSegunda = $valoresSegunda[1];  
		$anyoSegunda  = $valoresSegunda[0];

		$a = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
		$b = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);  
		$errorS = "Expiró la fecha límite de " . $branch->name;
		if($a - $b < 0)
		{
			
			Session::flash('error', $errorS);
			return Redirect::to("{$entityType}s/create")
				->withInput();
		}
		else
		{

		$last_invoice = Invoice::where('account_id', '=', Auth::user()->account_id)->first();
		if ($last_invoice)
		{
			$yesterday = $last_invoice->invoice_date;

			$today = date("Y-m-d", strtotime($invoice->invoice_date));

			$errorD = "La fecha de la factura es incorrecta";

			$yesterday = new DateTime($yesterday);
			$today = new DateTime($today);


			if($yesterday > $today)
			{			
				Session::flash('error', $errorD);
				return Redirect::to("{$entityType}s/create")
					->withInput();

			}
		}



		if ($errors = $this->invoiceRepo->getErrors($invoice))
		{					
			Session::flash('error', trans('texts.invoice_error'));

			return Redirect::to("{$entityType}s/create")
				->withInput()->withErrors($errors);
		} 
		else 
		{			
			$this->taxRateRepo->save($input->tax_rates);
						
			$clientData = (array) $invoice->client;	
			$clientData['branch'] = $branch->id;	
			$client = $this->clientRepo->save($invoice->client->public_id, $clientData);
						
			$invoiceData = (array) $invoice;
			$invoiceData['branch_id'] = $branch->id;
			$invoiceData['client_id'] = $client->id;
			$invoiceData['client_nit'] = $client->nit;
			$invoiceData['client_name'] = $client->name;
			$invoiceData['action'] = $action;

			$invoice = $this->invoiceRepo->save($publicId, $invoiceData, $entityType);
			
			$account = Auth::user()->account;
			// if ($account->invoice_taxes != $input->invoice_taxes 
			// 			|| $account->invoice_item_taxes != $input->invoice_item_taxes
			// 			|| $account->invoice_design_id != $input->invoice->invoice_design_id)
			// {
			// 	$account->invoice_taxes = $input->invoice_taxes;
			// 	$account->invoice_item_taxes = $input->invoice_item_taxes;
			// 	$account->invoice_design_id = $input->invoice->invoice_design_id;
			// 	$account->save();
			// }

			$client->load('contacts');
			$sendInvoiceIds = [];

			foreach ($client->contacts as $contact)
			{
				if ($contact->send_invoice || count($client->contacts) == 1)
				{	
					$sendInvoiceIds[] = $contact->id;
				}
			}
			
			foreach ($client->contacts as $contact)
			{
				$invitation = Invitation::scope()->whereContactId($contact->id)->whereInvoiceId($invoice->id)->first();
				
				if (in_array($contact->id, $sendInvoiceIds) && !$invitation) 
				{	
					$invitation = Invitation::createNew();
					$invitation->invoice_id = $invoice->id;
					$invitation->contact_id = $contact->id;
					$invitation->invitation_key = str_random(RANDOM_KEY_LENGTH);
					$invitation->save();
				}				
				else if (!in_array($contact->id, $sendInvoiceIds) && $invitation)
				{
					$invitation->delete();
				}
			}	



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
		    $qr->draw(150, 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png');
		    $input_file = 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png';
		    $output_file = 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.jpg';

		    $inputqr = imagecreatefrompng($input_file);
		    list($width, $height) = getimagesize($input_file);
		    $output = imagecreatetruecolor($width, $height);
		    $white = imagecolorallocate($output,  255, 255, 255);
		    imagefilledrectangle($output, 0, 0, $width, $height, $white);
		    imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
		    imagejpeg($output, $output_file);

		    $invoice->qr=HTML::image_data('qr/' . $account->account_key .'_'. $branch->name .'_'. $invoice->invoice_number . '.jpg');
			$invoice->save();				

			$message = trans($publicId ? "texts.updated_{$entityType}" : "texts.created_{$entityType}");
			if ($input->invoice->client->public_id == '-1')
			{
				$message = $message . ' ' . trans('texts.and_created_client');

				$url = URL::to('clients/' . $client->public_id);
				Utils::trackViewed($client->getDisplayName(), ENTITY_CLIENT, $url);
			}
			
			if ($action == 'clone')
			{
				return $this->cloneInvoice($publicId);
			}
			else if ($action == 'convert')
			{
				return $this->convertQuote($publicId);
			}
			else if ($action == 'email') 
			{	
				$aux = 0;
				foreach ($client->contacts as $contact)
				{
					if ($contact->email)
					{	
						$aux = 1;
					}
				}
				if($aux == 0)
				{
					$errorMessage = trans('El cliente no tiene Correo Electrónico.');
					Session::flash('error', $errorMessage);	
				}
				else
				{	
					if (Auth::user()->confirmed && !Auth::user()->isDemo())
					{
						$message = trans("texts.emailed_{$entityType}");
						$this->mailer->sendInvoice($invoice);
						Session::flash('message', $message);
					}
					else
					{
						$errorMessage = trans(Auth::user()->registered ? 'texts.confirmation_required' : 'texts.registration_required');
						Session::flash('error', $errorMessage);
						Session::flash('message', $message);					
					}
				}


			}
			else if ($action == 'savepay') 
			{	
					
		        $payment = Payment::createNew();
		        $payment->client_id = $client->id;
		        $payment->invoice_id = $invoice->id;
		        $payment->payment_type_id = 1;
		        $payment->payment_date = $invoice->invoice_date;
		        $payment->amount = $invoice->amount;
		        $payment->save();
				$message = trans("texts.savepay_{$entityType}");
				Session::flash('message', $message);


			}
			else if ($action == 'savepaycredit') 
			{	
					
		        $payment = Payment::createNew();

	            $credits = Credit::scope()->where('client_id', '=', $client->id)
	            			->where('balance', '>', 0)->orderBy('created_at')->get();            
	            $applied = 0;

	            foreach ($credits as $credit)
	            {
	                $applied += $credit->apply($invoice->amount);

	                if ($applied >= $invoice->amount)
	                {
	                    break;
	                }
	            }

		        $payment->client_id = $client->id;
		        $payment->invoice_id = $invoice->id;
		        $payment->payment_type_id = 2;
		        $payment->payment_date = $invoice->invoice_date;
		        $payment->amount = $invoice->amount;
		        $payment->save();
				$message = trans("texts.savepay_{$entityType}");
				Session::flash('message', $message);


			} 
			else 
			{				
				Session::flash('message', $message);
			}

			$url = "{$entityType}s/" . $invoice->public_id . '/edit';
			return Redirect::to($url);
		}
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($publicId)
	{
		Session::reflash();
		
		return Redirect::to('invoices/'.$publicId.'/edit');
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($publicId)
	{
		return InvoiceController::save($publicId);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function bulk($entityType = ENTITY_INVOICE)
	{
		$action = Input::get('action');
		$statusId = Input::get('statusId');
		$ids = Input::get('id') ? Input::get('id') : Input::get('ids');
		if($action == 'delete')
		{
			$invoices = Invoice::withTrashed()->scope($ids)->get();
			foreach ($invoices as $invoice) 
			{
				BookSale::deleteBook($invoice);
			}
		}
		$count = $this->invoiceRepo->bulk($ids, $action, $statusId);

 		if ($count > 0)		
 		{
 			$key = $action == 'mark' ? "updated_{$entityType}" : "{$action}d_{$entityType}";
			$message = Utils::pluralize($key, $count);
			Session::flash('message', $message);
		}

		return Redirect::to("{$entityType}s");
	}

	public function convertQuote($publicId)
	{
		$invoice = Invoice::with('invoice_items')->scope($publicId)->firstOrFail();   
		$clone = $this->invoiceRepo->cloneInvoice($invoice, $invoice->id);

		Session::flash('message', trans('texts.converted_to_invoice'));
		return Redirect::to('invoices/' . $clone->public_id);
	}

	public function cloneInvoice($publicId)
	{
		/*
		$invoice = Invoice::with('invoice_items')->scope($publicId)->firstOrFail();   
		$clone = $this->invoiceRepo->cloneInvoice($invoice);
		$entityType = $invoice->getEntityType();

		Session::flash('message', trans('texts.cloned_invoice'));
		return Redirect::to("{$entityType}s/" . $clone->public_id);
		*/

		return self::edit($publicId, true);
	}
	public function listasCuenta()
    {	
    	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')
    				// ->select('account_id','branch_id')


    				->where('id',$user_id)->first();


    	$branch = DB::table('branches')->where('id','=',$user->branch_id)->first();	
    	// $clients = DB::table('clients')->select('id','name','nit')->where('account_id',$user->account_id)->get(array('id','name','nit'));
    	// $account = DB::table('accounts')->where('id',$user->account_id) 	->first();

    	$products = DB::table('products')
    							// ->join('prices',"product_id","=",'products.id')
    							// ->select('products.id','products.product_key','products.notes','prices.cost')
    						    ->where('account_id','=',$user->account_id)
    						    // ->where('branch_id','=',$user->branch_id)
    						    // ->where('user_id','=',$user->id)
    							// ->where('prices.price_type_id','=',$user->price_type_id)
    							->get(array('id','product_key','notes','cost'));
    	
    							

    	// $ice = DB::table('tax_rates')->select('rate')
    	// 							 // ->where('account_id','=',$user->account_id)
    	// 							 ->where('name','=','ice')
    	// 							 ->first();


    	$mensaje = array(
    			//'clientes' => $clients,
    			//'user'=> $user,
    			'productos' => $products
    			//'ice'=>$ice->rate
    		);

    	return Response::json($mensaje);

    }
    
    
    public function guardarFacturaG()
    {
    	/* David 
    	 Guardando  factura con el siguiente formato:
    	
			{"invoice_items":[{"qty":"2","id":"2"}],"client_id":"1"}
			//nuevo formato para la cascada XD
			{"invoice_items":[{"qty":"2","id":"2","boni":"1","desc":"3"}],"client_id":"1"}

    	*/
		$input = Input::all();
        
		// $invoice_number = Auth::user()->account->getNextInvoiceNumber();
		$invoice_number = Auth::user()->branch->getNextInvoiceNumber();


		$client_id = $input['client_id'];
		$client = DB::table('clients')->select('id','nit','name','public_id','custom_value4')->where('id',$input['client_id'])->first();

		DB::table('clients')
				->where('id',$input['client_id'])
				->update(array('nit' => $input['nit'],'name'=>$input['name']));

		$user_id = Auth::user()->getAuthIdentifier();
		$user  = DB::table('users')->select('account_id','branch_id','public_id')->where('id',$user_id)->first();
		

		$account = DB::table('accounts')->where('id',$user->account_id)->first();
		// //$account_id = $user->account_id;
		// // $account = DB::table('accounts')->select('num_auto','llave_dosi','fecha_limite')->where('id',$user->account_id)->first();
		// //$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id',$user['branch_id'])->first();
		// //$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id','=',$user->branch_id)->first();	
  //   	// $branch = DB::table('branches')->select('number_autho','key_dosage','deadline','address1','address2','country_id','industry_id','law','activity_pri','activity_sec1','name')->where('id','=',$user->branch_id)->first();	
    	
		

    	$branch = DB::table('branches')->where('id','=',$user->branch_id)->first();	



    	$invoice_design = DB::table('invoice_designs')->select('id')
							->where('account_id','=',$user->account_id)
							// ->where('branch_id','=',$branch->public_id)
							// ->where('user_id','=',$user->public_id)
							->first();
    		// return Response::json($invoice_design);
    	$items = $input['invoice_items'];



    	// $linea ="";
    	$amount = 0;
    	$subtotal=0;
    	$fiscal=0;
    	$icetotal=0;
    	$bonidesc =0;
    	$productos = array();

    	foreach ($items as $item) 
    	{
    		# code...
    		$product_id = $item['id'];
    		 
    		$pr = DB::table('products')
    							// ->join('prices',"product_id","=",'products.id')
    					
    							// ->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    // ->where('prices.price_type_id','=',$user->price_type_id)
    						    // ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$product_id)

    							->first();
    		$pr->amount = $item['amount'];

    		// $pr->xd ='hola';
    		$amount = $amount + $pr->amount;
    		$pr->qty = 1;
    		$productos = $pr;					
    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
    		
    		// $qty = (int) $item['qty'];
    		// $cost = $pr->cost/$pr->units;
    		// $st = ($cost * $qty);
    		// $subtotal = $subtotal + $st; 
    		// $bd= ($item['boni']*$cost) + $item['desc'];
    		// $bonidesc= $bonidesc +$bd;
    		// $amount = $amount +$st-$bd;
    		
  //   			// $fiscal = $fiscal +$amount;

    			

    	}

  //   	$fiscal = $amount -$bonidesc-$icetotal;

    	$balance= 0;
  //   	/////////////////////////hasta qui esta bien al parecer hacer prueba de que fuciona el join de los productos XD
    	$invoice_dateCC = date("Ymd");
    	$invoice_date = date("Y-m-d");
    
		$invoice_date_limitCC = date("Y-m-d", strtotime($branch->deadline));

		require_once(app_path().'/includes/control_code.php');	
		$cod_control = codigoControl($invoice_number, $client->nit, $invoice_dateCC, $amount, $branch->number_autho, $branch->key_dosage);
	 //     $ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
	 //     //
	 //     // creando invoice
	     $invoice = Invoice::createNew();
	     $invoice->invoice_number=$invoice_number;
	     $invoice->client_id=$client_id;
	     $invoice->user_id=$user_id;
	     $invoice->account_id = $user->account_id;
	     $invoice->branch_id= $user->branch_id;
	     $invoice->amount =number_format((float)$amount, 2, '.', '');	
	      $invoice->subtotal =number_format((float)$amount, 2, '.', '');	
	     $invoice->invoice_design_id = $invoice_design->id;




//------------- hasta aqui funciona despues sale error

	     $invoice->law = $branch->law;
	     $invoice->balance=$balance;
	     $invoice->control_code=$cod_control;
	     $invoice->start_date =$invoice_date;
	     $invoice->invoice_date=$invoice_date;

		 $invoice->activity_pri=$branch->activity_pri;
	     $invoice->activity_sec1=$branch->activity_sec1;
	     
	 //     // $invoice->invoice
	     $invoice->end_date=$invoice_date_limitCC;
	 //     //datos de la empresa atra vez de una consulta XD
	 //     /*****************error generado al intentar guardar **/
	 //   	 // $invoice->branch = $branch->name;
	     $invoice->address1=$branch->address1;
	     $invoice->address2=$branch->address2;
	     $invoice->number_autho=$branch->number_autho; 
	     // $invoice->work_phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;
	 //     // $invoice->industry_id=$branch->industry_id;
 	
	     // $invoice->country_id= $branch->country_id;
	     $invoice->key_dosage = $branch->key_dosage;
	     $invoice->deadline = $branch->deadline;
	 //     $invoice->custom_value1 =$icetotal;
	 //     $invoice->ice = $ice->rate;
	 //     //cliente
	 //     $invoice->nit=$client->nit;
	 //     $invoice->name =$client->name;
	     //adicionales de la nueva plataforma
	     $invoice->account_name = $account->name;
	     $invoice->account_nit = $account->nit;

	     $invoice->client_name = $input['name'];
	     $invoice->client_nit = $input['nit'];

	     $invoice->phone = $branch->postal_code;



	     $invoice->save();
	     
	 //     $account = Auth::user()->account;
	  

		// 	$ice = $invoice->amount-$invoice->fiscal;
		// 	$desc = $invoice->subtotal-$invoice->amount;

		// 	$amount = number_format($invoice->amount, 2, '.', '');
		// 	$fiscal = number_format($invoice->fiscal, 2, '.', '');

		// 	$icef = number_format($ice, 2, '.', '');
		// 	$descf = number_format($desc, 2, '.', '');

		// 	if($icef=="0.00"){
		// 		$icef = 0;
		// 	}
		// 	if($descf=="0.00"){
		// 		$descf = 0;
		// 	}
	     	require_once(app_path().'/includes/BarcodeQR.php');
			 $icef = 0;
		    $descf = 0;

		    $qr = new BarcodeQR();
		    $datosqr = $invoice->account_nit.'|'.$invoice->invoice_number.'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$invoice->amount.'|'.$invoice->amount.'|'.$invoice->nit.'|'.$icef.'|0|0|'.$descf;
		    $qr->text($datosqr); 
		    $qr->draw(150, 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png');
		    $input_file = 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png';
		    $output_file = 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.jpg';

		    $inputqr = imagecreatefrompng($input_file);
		    list($width, $height) = getimagesize($input_file);
		    $output = imagecreatetruecolor($width, $height);
		    $white = imagecolorallocate($output,  255, 255, 255);
		    imagefilledrectangle($output, 0, 0, $width, $height, $white);
		    imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
		    imagejpeg($output, $output_file);

		    $invoice->qr=HTML::image_data('qr/' . $account->account_key .'_'. $branch->name .'_'. $invoice->invoice_number . '.jpg');
			$invoice->save();				
	     	 DB::table('invoices')
            ->where('id', $invoice->id)
            ->update(array('branch_name' => $branch->name));


            //generando pago

            $payment =Payment::createNew();
            $payment->invoice_id = $invoice->id;
            $payment->account_id = $invoice->account_id;
            $payment->client_id = $invoice->client_id;
            $payment->user_id = $invoice->user_id;
            $payment->payment_type_id = 2;
            $payment->amount = $invoice->amount;
            $payment->payment_date = $invoice->date;
            $payment->save();

            // -------------

            //descontando credito
            $credito = DB::table('credits')
            			->where('client_id',$client->id)->first();

            $monto =(int) ($credito->balance-$invoice->amount);
            DB::table('credits')->where('client_id',$client->id)
            					->update(array('balance'=>$monto));

            // return  Response::json($monto);
           
        

            // --------------------

	     //error verificar

	     // $invoice = DB::table('invoices')->select('id')->where('invoice_number',$invoice_number)->first();

	     //guardadndo los invoice items
	    foreach ($items as $item) 

    	{
    		
    		
    		
    		// $product = DB::table('products')->select('notes')->where('id',$product_id)->first();
    		  $product_id = $item['id'];
	    		 
	    		$product = DB::table('products')
    							// ->join('prices',"product_id","=",'products.id')
    					
    							// ->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    // ->where('prices.price_type_id','=',$user->price_type_id)
    						    // ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$product_id)

    							->first();

	    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
	    		
	    		
	    		// $cost = $product->cost/$product->units;
	    		// $line_total= ((int)$item['qty'])*$cost;

    		
    		  $invoiceItem = InvoiceItem::createNew();
    		  $invoiceItem->invoice_id = $invoice->id; 
		      $invoiceItem->product_id = $product_id;
		      $invoiceItem->product_key = $product->product_key;
		      $invoiceItem->notes = $product->notes;
		      $invoiceItem->cost = $item['amount'];
		      $invoiceItem->qty = 1;
		      // $invoiceItem->line_total=$line_total;
		      $invoiceItem->tax_rate = 0;
		      $invoiceItem->save();
		  
    	}
    	

    	$invoiceItems =DB::table('invoice_items')
    				   ->select('notes','cost','qty')
    				   ->where('invoice_id','=',$invoice->id)
    				   ->get(array('notes','cost','qty'));

    	$date = new DateTime($invoice->deadline);
    	$dateEmision = new DateTime($invoice->invoice_date);
    	$cuenta = array('name' =>$account->name,'nit'=>$account->nit );
    	// $ice = $invoice->amount-$invoice->fiscal;

    		// $factura  = array('invoice_number' => $invoice->invoice_number,
  //   					'control_code'=>$invoice->control_code,
  //   					'invoice_date'=>$dateEmision->format('d-m-Y'),
  //   					'amount'=>number_format((float)$invoice->amount, 2, '.', ''),
  //   					'subtotal'=>number_format((float)$invoice->subtotal, 2, '.', ''),
  //   					'fiscal'=>number_format((float)$invoice->fiscal, 2, '.', ''),
  //   					'client'=>$client,
  //   					// 'id'=>$invoice->id,

  //   					'account'=>$account,
  //   					'law' => $invoice->law,
  //   					'invoice_items'=>$invoiceItems,
  //   					'address1'=>str_replace('+', '°', $invoice->address1),
  //   					// 'address2'=>str_replace('+', '°', $invoice->address2),
  //   					'address2'=>$invoice->address2,
  //   					'num_auto'=>$invoice->number_autho,
  //   					'fecha_limite'=>$date->format('d-m-Y'),
  //   					// 'fecha_emsion'=>,
  //   					'ice'=>number_format((float)$ice, 2, '.', '')	
    					
  //   					);
    	$client->name = $input['name'];
    	$client->nit = $input['nit'];	
    	// $client->public_id = $client->custom_value4;	
    	$factura  = array('invoice_number' => $invoice->invoice_number,
    					'control_code'=>$invoice->control_code,
    					'activity_pri' => $branch->activity_pri,
    					'invoice_date'=>$dateEmision->format('d-m-Y'),
    					'amount'=>number_format((float)$invoice->amount, 2, '.', ''),
    					'subtotal'=>number_format((float)$invoice->subtotal, 2, '.', ''),
    					'fiscal'=>number_format((float)$invoice->fiscal, 2, '.', ''),
    					'client'=>$client,
    					// 'id'=>$invoice->id,

    					'account'=>$account,
    					'law' => $invoice->law,
    					'invoice_items'=>$invoiceItems,
    					'address1'=>str_replace('+', '°', $invoice->address1),
    					// 'address2'=>str_replace('+', '°', $invoice->address2),
    					'address2'=>$invoice->address2,
    					'num_auto'=>$invoice->number_autho,
    					'fecha_limite'=>$date->format('d-m-Y')
    					// 'fecha_emsion'=>,
    					// 'ice'=>number_format((float)$ice, 2, '.', '')	
    					
    					);

    	// $invoic = Invoice::scope($invoice_number)->withTrashed()->with('client.contacts', 'client.country', 'invoice_items')->firstOrFail();
		// $d  = Input::all();
		//en caso de problemas irracionales me refiero a que se jodio  
		// $input = Input::all();
		// $client_id = $input['client_id'];
		// $client = DB::table('clients')->select('id','nit','name')->where('id',$input['client_id'])->first();
		$input = Input::all();
		
		$datos = array('hola ' => 'mundo',
						'user'=>$user,
						'input' => $input,
						'invoice number' => $invoice_number,
						'client' => $client,
						'user' => $user,
						'branch' => $branch,
						'account' => $account,
						'invoice_design' => $invoice_design,
						'productos' => $productos

						);

		// return Response::json($datos);
		return Response::json($factura);
       
    }

     public function guardarFactura()
    {
    	/* David 
    	 Guardando  factura con el siguiente formato:
    	
			{"invoice_items":[{"qty":"2","id":"2"}],"client_id":"1"}
			//nuevo formato para la cascada XD
			{"invoice_items":[{"qty":"2","id":"2","boni":"1","desc":"3"}],"client_id":"1"}
			//para version generica

			{"invoice_items":[{"qty":"6","id":"11"}],"name":"Sin Nombre","nit":"0","client_id":"19"}

    	*/
		$input = Input::all();
        
		// $invoice_number = Auth::user()->account->getNextInvoiceNumber();
		$invoice_number = Auth::user()->branch->getNextInvoiceNumber();


		$client_id = $input['client_id'];
		$client = DB::table('clients')->select('id','nit','name','public_id')->where('id',$input['client_id'])->first();
		DB::table('clients')
				->where('id',$input['client_id'])
				->update(array('nit' => $input['nit'],'name'=>$input['name']));

		//

		$user_id = Auth::user()->getAuthIdentifier();
		$user  = DB::table('users')->select('account_id','branch_id','public_id')->where('id',$user_id)->first();
		

		$account = DB::table('accounts')->where('id',$user->account_id)->first();
		// //$account_id = $user->account_id;
		// // $account = DB::table('accounts')->select('num_auto','llave_dosi','fecha_limite')->where('id',$user->account_id)->first();
		// //$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id',$user['branch_id'])->first();
		// //$branch = DB::table('branches')->select('num_auto','llave_dosi','fecha_limite','address1','address2','country_id','industry_id')->where('id','=',$user->branch_id)->first();	
  //   	// $branch = DB::table('branches')->select('number_autho','key_dosage','deadline','address1','address2','country_id','industry_id','law','activity_pri','activity_sec1','name')->where('id','=',$user->branch_id)->first();	
    	
		

    	$branch = DB::table('branches')->where('id','=',$user->branch_id)->first();	



    	$invoice_design = DB::table('invoice_designs')->select('id')
							->where('account_id','=',$user->account_id)
							// ->where('branch_id','=',$branch->public_id)
							// ->where('user_id','=',$user->public_id)
							->first();
    		// return Response::json($invoice_design);
    	$items = $input['invoice_items'];



    	// $linea ="";
    	$amount = 0;
    	$subtotal=0;
    	$fiscal=0;
    	$icetotal=0;
    	$bonidesc =0;
    	$productos = array();

    	foreach ($items as $item) 
    	{
    		# code...
    		$product_id = $item['id'];
    		 
    		$pr = DB::table('products')
    							// ->join('prices',"product_id","=",'products.id')
    					
    							// ->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    // ->where('prices.price_type_id','=',$user->price_type_id)
    						    // ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$product_id)

    							->first();
    	

    		// $pr->xd ='hola';
    		//me quede aqui me llego sueñito XD
    		$amount = $amount +($pr->cost * $item['qty']);
    		// $pr->qty = 1;
    		$productos = $pr;					
    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
    		
    		// $qty = (int) $item['qty'];
    		// $cost = $pr->cost/$pr->units;
    		// $st = ($cost * $qty);
    		// $subtotal = $subtotal + $st; 
    		// $bd= ($item['boni']*$cost) + $item['desc'];
    		// $bonidesc= $bonidesc +$bd;
    		// $amount = $amount +$st-$bd;
    		
  //   			// $fiscal = $fiscal +$amount;

    			

    	}

  //   	$fiscal = $amount -$bonidesc-$icetotal;

    	$balance= $amount;
    	$subtotal = $amount;
  //   	/////////////////////////hasta qui esta bien al parecer hacer prueba de que fuciona el join de los productos XD
    	$invoice_dateCC = date("Ymd");
    	$invoice_date = date("Y-m-d");
    
		$invoice_date_limitCC = date("Y-m-d", strtotime($branch->deadline));

		require_once(app_path().'/includes/control_code.php');	
		$cod_control = codigoControl($invoice_number, $client->nit, $invoice_dateCC, $amount, $branch->number_autho, $branch->key_dosage);
	 //     $ice = DB::table('tax_rates')->select('rate')->where('name','=','ice')->first();
	 //     //
	 //     // creando invoice
	     $invoice = Invoice::createNew();
	     $invoice->invoice_number=$invoice_number;
	     $invoice->client_id=$client_id;
	     $invoice->user_id=$user_id;
	     $invoice->account_id = $user->account_id;
	     $invoice->branch_id= $user->branch_id;
	     $invoice->amount =number_format((float)$amount, 2, '.', '');	
	     $invoice->invoice_design_id = $invoice_design->id;

//------------- hasta aqui funciona despues sale error

	     $invoice->law = $branch->law;
	     $invoice->balance=$balance;
	     $invoice->subtotal = $subtotal;
	     $invoice->control_code=$cod_control;
	     $invoice->start_date =$invoice_date;
	     $invoice->invoice_date=$invoice_date;

		 $invoice->activity_pri=$branch->activity_pri;
	     $invoice->activity_sec1=$branch->activity_sec1;
	     
	 //     // $invoice->invoice
	     $invoice->end_date=$invoice_date_limitCC;
	 //     //datos de la empresa atra vez de una consulta XD
	 //     /*****************error generado al intentar guardar **/
	 //   	 // $invoice->branch = $branch->name;
	     $invoice->address1=$branch->address1;
	     $invoice->address2=$branch->address2;
	     $invoice->number_autho=$branch->number_autho; 
	     // $invoice->work_phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;
	 //     // $invoice->industry_id=$branch->industry_id;
 	
	     // $invoice->country_id= $branch->country_id;
	     $invoice->key_dosage = $branch->key_dosage;
	     $invoice->deadline = $branch->deadline;
	 //     $invoice->custom_value1 =$icetotal;
	 //     $invoice->ice = $ice->rate;
	 //     //cliente
	 //     $invoice->nit=$client->nit;
	 //     $invoice->name =$client->name;
	     //adicionales de la nueva plataforma
	     $invoice->account_name = $account->name;
	     $invoice->account_nit = $account->nit;

	     $invoice->client_name = $input['name'];
	     $invoice->client_nit = $input['nit'];

	     $invoice->phone = $branch->postal_code;



	     $invoice->save();
	     
	 //     $account = Auth::user()->account;
	  

		// 	$ice = $invoice->amount-$invoice->fiscal;
		// 	$desc = $invoice->subtotal-$invoice->amount;

		// 	$amount = number_format($invoice->amount, 2, '.', '');
		// 	$fiscal = number_format($invoice->fiscal, 2, '.', '');

		// 	$icef = number_format($ice, 2, '.', '');
		// 	$descf = number_format($desc, 2, '.', '');

		// 	if($icef=="0.00"){
		// 		$icef = 0;
		// 	}
		// 	if($descf=="0.00"){
		// 		$descf = 0;
		// 	}
	     	require_once(app_path().'/includes/BarcodeQR.php');
			 $icef = 0;
		    $descf = 0;

		    $qr = new BarcodeQR();
		    $datosqr = $invoice->account_nit.'|'.$invoice->invoice_number.'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$invoice->amount.'|'.$invoice->amount.'|'.$invoice->nit.'|'.$icef.'|0|0|'.$descf;
		    $qr->text($datosqr); 
		    $qr->draw(150, 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png');
		    $input_file = 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.png';
		    $output_file = 'qr/' . $account->account_key .'_'. $branch->name .'_'.  $invoice->invoice_number . '.jpg';

		    $inputqr = imagecreatefrompng($input_file);
		    list($width, $height) = getimagesize($input_file);
		    $output = imagecreatetruecolor($width, $height);
		    $white = imagecolorallocate($output,  255, 255, 255);
		    imagefilledrectangle($output, 0, 0, $width, $height, $white);
		    imagecopy($output, $inputqr, 0, 0, 0, 0, $width, $height);
		    imagejpeg($output, $output_file);

		    $invoice->qr=HTML::image_data('qr/' . $account->account_key .'_'. $branch->name .'_'. $invoice->invoice_number . '.jpg');
			$invoice->save();				
	     	 DB::table('invoices')
            ->where('id', $invoice->id)
            ->update(array('branch_name' => $branch->name));



	     //error verificar

	     // $invoice = DB::table('invoices')->select('id')->where('invoice_number',$invoice_number)->first();

	     //guardadndo los invoice items
	    foreach ($items as $item) 

    	{
    		
    		
    		
    		// $product = DB::table('products')->select('notes')->where('id',$product_id)->first();
    		  $product_id = $item['id'];
	    		 
	    		$product = DB::table('products')
    							// ->join('prices',"product_id","=",'products.id')
    					
    							// ->select('products.id','products.notes','prices.cost','products.ice','products.units','products.cc')
    						    // ->where('prices.price_type_id','=',$user->price_type_id)
    						    // ->where('products.account_id','=',$user->account_id)
    						    ->where('products.id',"=",$product_id)

    							->first();

	    		// $pr = DB::table('products')->select('cost')->where('id',$product_id)->first();
	    		
	    		
	    		// $cost = $product->cost/$product->units;
	    		// $line_total= ((int)$item['qty'])*$cost;

    		
    		  $invoiceItem = InvoiceItem::createNew();
    		  $invoiceItem->invoice_id = $invoice->id; 
		      $invoiceItem->product_id = $product_id;
		      $invoiceItem->product_key = $product->product_key;
		      $invoiceItem->notes = $product->notes;
		      $invoiceItem->cost = $product->cost;
		      $invoiceItem->qty = $item['qty'];
		      // $invoiceItem->line_total=$line_total;
		      $invoiceItem->tax_rate = 0;
		      $invoiceItem->save();
		  
    	}
    	

    	$invoiceItems =DB::table('invoice_items')
    				   ->select('notes','cost','qty')
    				   ->where('invoice_id','=',$invoice->id)
    				   ->get(array('notes','cost','qty'));

    	$date = new DateTime($invoice->deadline);
    	$dateEmision = new DateTime($invoice->invoice_date);
    	$cuenta = array('name' =>$account->name,'nit'=>$account->nit );
    	// $ice = $invoice->amount-$invoice->fiscal;

    		// $factura  = array('invoice_number' => $invoice->invoice_number,
  //   					'control_code'=>$invoice->control_code,
  //   					'invoice_date'=>$dateEmision->format('d-m-Y'),
  //   					'amount'=>number_format((float)$invoice->amount, 2, '.', ''),
  //   					'subtotal'=>number_format((float)$invoice->subtotal, 2, '.', ''),
  //   					'fiscal'=>number_format((float)$invoice->fiscal, 2, '.', ''),
  //   					'client'=>$client,
  //   					// 'id'=>$invoice->id,

  //   					'account'=>$account,
  //   					'law' => $invoice->law,
  //   					'invoice_items'=>$invoiceItems,
  //   					'address1'=>str_replace('+', '°', $invoice->address1),
  //   					// 'address2'=>str_replace('+', '°', $invoice->address2),
  //   					'address2'=>$invoice->address2,
  //   					'num_auto'=>$invoice->number_autho,
  //   					'fecha_limite'=>$date->format('d-m-Y'),
  //   					// 'fecha_emsion'=>,
  //   					'ice'=>number_format((float)$ice, 2, '.', '')	
    					
  //   					);

    	$client->name = $input['name'];
    	$client->nit = $input['nit'];			
    	$factura  = array('invoice_number' => $invoice->invoice_number,
    					'control_code'=>$invoice->control_code,
    					'invoice_date'=>$dateEmision->format('d-m-Y'),
    					
    					'activity_pri' => $branch->activity_pri,
    					'amount'=>number_format((float)$invoice->amount, 2, '.', ''),
    					'subtotal'=>number_format((float)$invoice->balance, 2, '.', ''),
    					'fiscal'=>number_format((float)$invoice->fiscal, 2, '.', ''),
    					'client'=>$client,
    					// 'id'=>$invoice->id,

    					'account'=>$account,
    					'law' => $invoice->law,
    					'invoice_items'=>$invoiceItems,
    					'address1'=>str_replace('+', '°', $invoice->address1),
    					// 'address2'=>str_replace('+', '°', $invoice->address2),
    					'address2'=>$invoice->address2,
    					'num_auto'=>$invoice->number_autho,
    					'fecha_limite'=>$date->format('d-m-Y')
    					// 'fecha_emsion'=>,
    					// 'ice'=>number_format((float)$ice, 2, '.', '')	
    					
    					);

    	// $invoic = Invoice::scope($invoice_number)->withTrashed()->with('client.contacts', 'client.country', 'invoice_items')->firstOrFail();
		// $d  = Input::all();
		//en caso de problemas irracionales me refiero a que se jodio  
		// $input = Input::all();
		// $client_id = $input['client_id'];
		// $client = DB::table('clients')->select('id','nit','name')->where('id',$input['client_id'])->first();


		return Response::json($factura);
       
    }
	
}	