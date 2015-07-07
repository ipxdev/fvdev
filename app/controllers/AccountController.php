<?php

use ninja\repositories\AccountRepository;
use ninja\mailers\UserMailer;
use ninja\mailers\ContactMailer;

class AccountController extends \BaseController {

	protected $accountRepo;
	protected $userMailer;
	protected $contactMailer;

	public function __construct(AccountRepository $accountRepo, UserMailer $userMailer, ContactMailer $contactMailer)
	{
		parent::__construct();

		$this->accountRepo = $accountRepo;
		$this->userMailer = $userMailer;
		$this->contactMailer = $contactMailer;
	}	
	
	public function getStarted()
	{

	$code = Input::get('code');

	if ($code == IPX_KEY)
	{
		
		if (Auth::check())
		{
			return Redirect::to('dashboard');	
		}

  //   	$account = DB::table('accounts')->select('pro_plan_paid')->orderBy('id', 'desc')->first();
  //   	if($account)
  //   	{
		// 	$datePaid = $account->pro_plan_paid;
		// 	if (!$datePaid || $datePaid == '0000-00-00')
		// 	{
		// 		return 'Vuelva a intentarlo mas tarde';
		// 	}
		// }

		$account = new Account;
		$account->ip = Request::getClientIp();
		$account->account_key = str_random(RANDOM_KEY_LENGTH);

		$account->nit= trim(Input::get('nit'));
		$account->name = trim(Input::get('name'));
		$account->language_id = 1;

		$account->save();
		
		$user = new User;
		$username = trim(Input::get('username'));
		$user->username = $username . "@" . $account->nit;
		$user->password = trim(Input::get('password'));
		$user->password_confirmation = trim(Input::get('password'));

		$user->registered = true;
		$user->is_admin = true;
		$account->users()->save($user);

		$category = new Category;
		$category->user_id =$user->getId();
		$category->name = "General";
		$category->public_id = 1;
		$account->categories()->save($category);

		$InvoiceDesign = new InvoiceDesign;
		$InvoiceDesign->user_id =$user->getId();
		$InvoiceDesign->logo = "";
		$InvoiceDesign->x = "5";
		$InvoiceDesign->y = "5";
		$InvoiceDesign->javascript = "displaytittle(doc, invoice, layout);

displayHeader(doc, invoice, layout);

doc.setFontSize(11);
doc.setFontType('normal');

var activi = invoice.economic_activity;
var activityX = 565 - (doc.getStringUnitWidth(activi) * doc.internal.getFontSize());
doc.text(activityX, layout.headerTop+45, activi);

var aleguisf_date = getInvoiceDate(invoice);

layout.headerTop = 50;
layout.tableTop = 190;
doc.setLineWidth(0.8);        
doc.setFillColor(255, 255, 255);
doc.roundedRect(layout.marginLeft - layout.tablePadding, layout.headerTop+95, 572, 35, 2, 2, 'FD');

var marginLeft1=30;
var marginLeft2=80;
var marginLeft3=180;
var marginLeft4=220;

datos1y = 160;
datos1xy = 15;
doc.setFontSize(11);
doc.setFontType('bold');
doc.text(marginLeft1, datos1y, 'Fecha : ');
doc.setFontType('normal');

doc.text(marginLeft2-5, datos1y, aleguisf_date);

doc.setFontType('bold');
doc.text(marginLeft1, datos1y+datos1xy, 'Señor(es) :');
doc.setFontType('normal');
doc.text(marginLeft2+15, datos1y+datos1xy, invoice.client_name);

doc.setFontType('bold');
doc.text(marginLeft3+240, datos1y+datos1xy, 'NIT/CI :');
doc.setFontType('normal');
doc.text(marginLeft4+245, datos1y+datos1xy, invoice.client_nit);

doc.setDrawColor(241,241,241);
doc.setFillColor(241,241,241);
doc.rect(layout.marginLeft - layout.tablePadding, layout.headerTop+140, 572, 20, 'FD');

doc.setFontSize(10);
doc.setFontType('bold');

if(invoice.branch_type_id==1)
{

    displayInvoiceHeader2(doc, invoice, layout);
	var y = displayInvoiceItems2(doc, invoice, layout);
	displayQR(doc, layout, invoice, y);
	y += displaySubtotals2(doc, layout, invoice, y+15, layout.unitCostRight+35);
}
if(invoice.branch_type_id==2)
{
    displayInvoiceHeader2(doc, invoice, layout);
	var y = displayInvoiceItems2(doc, invoice, layout);
	displayQR(doc, layout, invoice, y);
	y += displaySubtotals2(doc, layout, invoice, y+15, layout.unitCostRight+35);
}

y -=10;		
displayNotesAndTerms(doc, layout, invoice, y);";

		$account->invoice_designs()->save($InvoiceDesign);

		$user = $account->users()->first();
		
		Session::forget(RECENTLY_VIEWED);

		Auth::login($user, true);
		Event::fire('user.login');		

		return RESULT_SUCCESS;	

		}
	}

	public function enableProPlan()
	{		
		
		$code = Input::get('code');

		$result = $this->accountRepo->enableProPlan();

		if ($code == "123")
		{
			return RESULT_SUCCESS;	
		}
	
	}

	public function enablePlan()
	{		
		$user = Auth::user();
		$user->confirmation_code = '';
		$user->confirmed = true;
		$user->amend();

		$result = $this->accountRepo->enablePlan();

		if ($result)
		{
			return RESULT_SUCCESS;	
		}
	
	}

	public function enableProPlan2()
	{		
		$rules = array(
			'nroFactura' => 'required',
			'nit_ci' => 'required',
			'dia' => 'required|between:1,31',
			'mes' => 'required|between:1,12',
			'anio' => 'required',
			'monto' => 'required',
			'llave' => 'required',
			'nroAutorizacion' => 'required',

		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) 
		{
			return 'error';
		} 
		else 
		{
			$nroFactura = Input::get('nroFactura');
			$nit_ci = Input::get('nit_ci');
			$dia = Input::get('dia');
			$mes = Input::get('mes');
			$anio = Input::get('anio');
			$fecha = $anio . $mes . $dia;
			$monto = Input::get('monto');
			$nroAutorizacion = Input::get('nroAutorizacion');
			$llave = Input::get('llave');

			require_once(app_path().'/includes/control_code.php');
	    	$cod_control = codigoControl($nroFactura, $nit_ci, $fecha, $monto, $nroAutorizacion, $llave);

				return $cod_control;	
		}
	
	}

	public function setTrashVisible($entityType, $visible)
	{
		Session::put("show_trash:{$entityType}", $visible == 'true');
		return Redirect::to("{$entityType}s");
	}

	public function getSearchData()
	{
		$data = $this->accountRepo->getSearchData();

		return Response::json($data);
	}

	public function showSection($section = ACCOUNT_DETAILS, $subSection = false)  
	{
		if(Auth::user()->is_admin)
		{

				if ($section == ACCOUNT_DETAILS)
				{			
					$data = [
						'account' => Account::with('users')->findOrFail(Auth::user()->account_id),
						'showUser' => Auth::user()->id === Auth::user()->account->users()->first()->id,
						'b' => '',
					];

					return View::make('accounts.details', $data);
				}
				else if ($section == ACCOUNT_BRANCHES)
				{
					$data = [
						'account' => Auth::user()->account
					];

					return View::make('accounts.branches', $data);		
				}
				else if ($section == ACCOUNT_CATEGORIES)
				{
					$data = [
						'account' => Auth::user()->account
					];

					return View::make('accounts.categories', $data);		
				}
				else if ($section == ACCOUNT_MANUALS)
				{
					$data = [
						'account' => Auth::user()->account
					];

					return View::make('accounts.manuals', $data);		
				}
				else if ($section == ACCOUNT_USERS)
				{
					$data = [
						'account' => Auth::user()->account
					];

					return View::make('accounts.user_management', $data);		
				}
				else if ($section == ACCOUNT_PAYMENTS)
				{
					$account = Account::with('account_gateways')->findOrFail(Auth::user()->account_id);
					$accountGateway = null;
					$config = null;
					$configFields = null;
		    		$selectedCards = 0;

					if (count($account->account_gateways) > 0)
					{
						$accountGateway = $account->account_gateways[0];
						$config = $accountGateway->config;
						$selectedCards = $accountGateway->accepted_credit_cards;
		                
						$configFields = json_decode($config);
		                
						foreach($configFields as $configField => $value)
						{
							$configFields->$configField = str_repeat('*', strlen($value));
						}
					} else {
						$accountGateway = AccountGateway::createNew();
						$accountGateway->gateway_id = GATEWAY_MOOLAH;				
					}
					
					$recommendedGateways = Gateway::remember(DEFAULT_QUERY_CACHE)
							->where('recommended', '=', '1')
							->orderBy('sort_order')
							->get();
					$recommendedGatewayArray = array();
					
					foreach($recommendedGateways as $recommendedGateway)
					{
						$arrayItem = array(
							'value' => $recommendedGateway->id,
							'other' => 'false',
							'data-imageUrl' => asset($recommendedGateway->getLogoUrl()),
							'data-siteUrl' => $recommendedGateway->site_url
						);
						$recommendedGatewayArray[$recommendedGateway->name] = $arrayItem;
					}      

		     	$creditCardsArray = unserialize(CREDIT_CARDS);
		     	$creditCards = [];
					foreach($creditCardsArray as $card => $name)
					{
		        if($selectedCards > 0 && ($selectedCards & $card) == $card)
		            $creditCards[$name['text']] = ['value' => $card, 'data-imageUrl' => asset($name['card']), 'checked' => 'checked'];
		        else
		            $creditCards[$name['text']] = ['value' => $card, 'data-imageUrl' => asset($name['card'])];
					}

					$otherItem = array(
						'value' => 1000000,
						'other' => 'true',
						'data-imageUrl' => '',
						'data-siteUrl' => ''
					);
					$recommendedGatewayArray['Other Options'] = $otherItem;

					$gateways = Gateway::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get();

					foreach ($gateways as $gateway)
					{
						$paymentLibrary = $gateway->paymentlibrary;

						$gateway->fields = $gateway->getFields();	
			
						if ($accountGateway && $accountGateway->gateway_id == $gateway->id)
						{
							$accountGateway->fields = $gateway->fields;						
						}
					}	
		      
		      $data = [
						'account' => $account,
						'accountGateway' => $accountGateway,
						'config' => $configFields,
						'gateways' => $gateways,
						'dropdownGateways' => Gateway::remember(DEFAULT_QUERY_CACHE)
							->where('recommended', '=', '0')
							->orderBy('name')
							->get(),
						'recommendedGateways' => $recommendedGatewayArray,
		        'creditCardTypes' => $creditCards, 
					];
					
					return View::make('accounts.payments', $data);
				}
				else if ($section == ACCOUNT_NOTIFICATIONS)
				{
					$data = [
						'account' => Account::with('users')->findOrFail(Auth::user()->account_id),
					];

					return View::make('accounts.notifications', $data);
				}
				else if ($section == ACCOUNT_IMPORT_EXPORT)
				{
					return View::make('accounts.import_export');	
				}
				else if ($section == ACCOUNT_EXPORT_BOOK)
				{
					return View::make('accounts.export_book');	
				}
				else if ($section == ACCOUNT_IMPORT_EXPORTC)
				{
					return View::make('accounts.import_exportc');	
				}

				else if ($section == ACCOUNT_INVOICE_SETTINGS)
				{
					$data = [
						'account' => Auth::user()->account
					];
					return View::make('accounts.invoice_settings', $data);
				}

				else if ($section == ACCOUNT_PRODUCT_SETTINGS)
				{
					$data = [
						'account' => Auth::user()->account
					];
					return View::make('accounts.product_settings', $data);
				}

				else if ($section == ACCOUNT_INVOICE_DESIGN) 
				{
					$invoiceDesign = DB::table('invoice_designs')->where('account_id',\Auth::user()->account_id)->orderBy('public_id', 'desc')->first();
					$branches = Branch::where('account_id', '=', Auth::user()->account_id)->orderBy('id')->get();

					$data = [
						'branches' => $branches,
						'invoiceDesign' => $invoiceDesign
					];			
					$invoice = new stdClass();
					$client = new stdClass();
					$invoiceItem = new stdClass();				
					
					$client->name = 'Client';
					$client->address1 = '';
					$client->city = '';
					$client->state = '';
					$client->postal_code = '';
					$client->work_phone = '';
					$client->work_email = '';
					$client->custom_value2 = '000';
				
					$invoice->account_name = Auth::user()->account->getName();
					$invoice->account_nit = Auth::user()->account->getNit();
					$invoice->account_uniper = Auth::user()->account->getUniper();
					$invoice->invoice_number = '0000001';

					$invoice->invoice_date = date_create()->format('M d, Y');
					$invoice->client_name = 'Cliente';
					$invoice->client_nit = '123456789';

					$invoice->account = json_decode(Auth::user()->account->toJson());
					$invoice->amount = $invoice->balance = 5000;	
					$invoice->control_code = '24-1A-9A-89-9E';

					$invoice->qr = "data:image/jpeg;base64,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";

					$invoiceItem->cost = 5000;
					$invoiceItem->qty = 1;
					$invoiceItem->notes = 'Servicios';
					$invoiceItem->product_key = '100';				
					$invoice->client = $client;
					$invoice->invoice_items = [$invoiceItem];			
					
					$data['invoice'] = $invoice;


					return View::make("accounts.invoice_design", $data);	
				}
				else if ($section == ACCOUNT_PRODUCTS)
				{
					$data = [
						'account' => Auth::user()->account
					];

					return View::make('accounts.products', $data);		
				}
		}
		else
		{
			return Redirect::to('dashboard');
		}
	}

	public function doSection($section = ACCOUNT_DETAILS, $subSection = false)
	{
		if ($section == ACCOUNT_DETAILS)
		{
			return AccountController::saveDetails();
		}
		else if ($section == ACCOUNT_PAYMENTS)
		{
			return AccountController::savePayments();
		}
		else if ($section == ACCOUNT_IMPORT_EXPORT)
		{
			return AccountController::importFile();
		}
		else if ($section == ACCOUNT_MAP)
		{
			return AccountController::mapFile();
		}
		else if ($section == ACCOUNT_IMPORT_EXPORTI)
		{
			return AccountController::importFilei();
		}
		else if ($section == ACCOUNT_MAP_INVOICES)
		{
			return AccountController::mapFileInvoice();
		}
		else if ($section == ACCOUNT_NOTIFICATIONS)
		{
			return AccountController::saveNotifications();
		}		
		else if ($section == ACCOUNT_EXPORT)
		{
			return AccountController::export();
		}		

		else if ($section == ACCOUNT_INVOICE_SETTINGS) 
		{
			return AccountController::saveInvoiceSettings();
		} 
		else if ($section == ACCOUNT_PRODUCT_SETTINGS) 
		{
			return AccountController::saveProducts();
		} 
		else if ($section == ACCOUNT_INVOICE_DESIGN)
		{
			return AccountController::saveInvoiceDesign();
		}

		else if ($section == ACCOUNT_PRODUCTS)
		{
			return AccountController::saveProducts();
		}
	}

	private function saveProducts()
	{
		$account = Auth::user()->account;

		$account->fill_products = Input::get('fill_products') ? true : false;
		$account->update_products = Input::get('update_products') ? true : false;
		$account->save();

		Session::flash('message', trans('texts.updated_settings'));
		return Redirect::to('company/product_settings');		
	}

	private function saveInvoiceSettings()
	{

			$account = Auth::user()->account;
			
			$account->custom_label1 = trim(Input::get('custom_label1'));
			$account->custom_value1 = trim(Input::get('custom_value1'));
			$account->custom_label2 = trim(Input::get('custom_label2'));
			$account->custom_value2 = trim(Input::get('custom_value2'));

			$account->custom_client_label1 = trim(Input::get('custom_client_label1'));
			$account->custom_client_label2 = trim(Input::get('custom_client_label2'));	
			$account->custom_client_label3 = trim(Input::get('custom_client_label3'));	
			$account->custom_client_label4 = trim(Input::get('custom_client_label4'));	
			$account->custom_client_label5 = trim(Input::get('custom_client_label5'));	
			$account->custom_client_label6 = trim(Input::get('custom_client_label6'));	
			$account->custom_client_label7 = trim(Input::get('custom_client_label7'));	
			$account->custom_client_label8 = trim(Input::get('custom_client_label8'));	
			$account->custom_client_label9 = trim(Input::get('custom_client_label9'));	
			$account->custom_client_label10 = trim(Input::get('custom_client_label10'));	
			$account->custom_client_label11 = trim(Input::get('custom_client_label11'));
			$account->custom_client_label12 = trim(Input::get('custom_client_label12'));

			$account->save();
			
			Session::flash('message', trans('texts.updated_settings'));
			return Redirect::to('company/invoice_settings');		
	}

	private function saveInvoiceDesign()
	{
				
				$account = Auth::user()->account;
			    $account->op3 = true;
				$account->save();

				if ($file = Input::file('logo') || Input::get('design')|| Input::get('x'))
				{
					if (Auth::user()->account->isRegistered())
					{
				        $invoice_design = InvoiceDesign::createNew();
				        $invoice_design_old = InvoiceDesign::scope()->orderBy('public_id', 'desc')->firstOrFail();
						$invoice_design->javascript = $invoice_design_old->javascript;
						$invoice_design->x = $invoice_design_old->x;
						$invoice_design->y = $invoice_design_old->y;
						$invoice_design->logo = $invoice_design_old->logo;

					}
				    else
				    {
				        $invoice_design = InvoiceDesign::scope()->orderBy('public_id', 'desc')->firstOrFail();
				    }
			    
				    if ($file = Input::file('logo'))
					{
						$path = Input::file('logo')->getRealPath();
						File::delete('logo/' . $account->account_key . '.jpg');		

						$image = Image::make($path)->resize(200, 120, true, false);
						Image::canvas($image->width, $image->height, '#FFFFFF')->insert($image)->save($account->getLogoPath());
						$invoice_design->logo = HTML::image_data('logo/' . $account->account_key . '.jpg');
						File::delete('logo/' . $account->account_key . '.jpg');	
						$invoice_design->save();
											}
					if (Input::get('x')|| Input::get('y'))
					{

						$invoice_design->x = Input::get('x');
						$invoice_design->y = Input::get('y');		
						$invoice_design->save();	

					}
					if (Input::get('design'))
					{

						$invoice_design->javascript = Input::get('design');
						$invoice_design->save();
					}

					Session::flash('message', trans('texts.updated_settings'));		
				}
				return Redirect::to('company/invoice_design');	

	}

	private function export()
	{
		$fecha = Input::get('invoice_date');

		$dt = Carbon::parse($fecha);
		$month = $dt->month;
		$year = $dt->year;
		$fechaSearch = $month . "/" . $year;

		$output = fopen('php://output','w') or Utils::fatalError();
		header('Content-Type:application/csv'); 
		header('Content-Disposition:attachment;filename=Libro_Ventas.csv');
		
		$BookSale = DB::table('book_sales')
		->select('client_nit', 'client_name', 'invoice_number', 'number_autho', 'invoice_date', 'amount', 'ice_amount', 'grav_amount', 'base_fiscal_debit_amount', 'fiscal_debit_amount', 'status', 'control_code')
		->where('account_id','=',Auth::user()->account_id)
		->where('invoice_date','LIKE','%'.$fechaSearch)
 		->orderBy('invoice_date', 'asc')
		->get();

		AccountController::exportBooksale($output, Utils::toArray($BookSale));

		fclose($output);
		exit;
	}

	private function exportnew()
	{
		$fecha = Input::get('invoice_date');

		$dt = Carbon::parse($fecha);
		$month = $dt->month;
		$year = $dt->year;
		$fechaSearch = $month . "/" . $year;

		$output = fopen('php://output','w') or Utils::fatalError();
		header('Content-Type:application/csv'); 
		header('Content-Disposition:attachment;filename=Libro_Ventas.csv');
		
		$BookSale = DB::table('book_sales')
		->select('invoice_date', 'invoice_number', 'number_autho', 'status', 'client_nit', 'client_name', 'amount', 'ice_amount', 'export_amount', 'grav_amount', 'subtotal', 'disc_bonus_amount', 'base_fiscal_debit_amount', 'fiscal_debit_amount', 'control_code')
		->where('account_id','=',Auth::user()->account_id)
		->where('invoice_date','LIKE','%'.$fechaSearch)
 		->orderBy('invoice_date', 'asc')
		->get();

		AccountController::exportBooksale($output, Utils::toArray($BookSale));

		fclose($output);
		exit;
	}

	private function exportBooksale($output, $data)
	{
		// if (count($data) > 0)
		// {
		// 	fputcsv($output, array_keys($data[0]));
		// }

		foreach($data as $record) 
		{
		    fputcsv($output, $record,"|");
		}

		fwrite($output, "\n");
	}

	private function exportData($output, $data)
	{
		if (count($data) > 0)
		{
			fputcsv($output, array_keys($data[0]));
		}

		foreach($data as $record) 
		{
		    fputcsv($output, $record);
		}

		fwrite($output, "\n");
	}

	private function importFile()
	{
		$data = Session::get('data');
		Session::forget('data');

		$map = Input::get('map');
		$count = 0;
		$hasHeaders = Input::get('header_checkbox');
		
		$countries = Country::remember(DEFAULT_QUERY_CACHE)->get();
		$countryMap = [];

		foreach ($countries as $country) 
		{
			$countryMap[strtolower($country->name)] = $country->id;
		}		

		foreach ($data as $row)
		{
			if ($hasHeaders)
			{
				$hasHeaders = false;
				continue;
			}

			$client = Client::createNew();		
			$contact = Contact::createNew();
			$contact->is_primary = true;
			$contact->send_invoice = true;
			$count++;

			foreach ($row as $index => $value)
			{
				$field = $map[$index];
				$value = trim($value);

				if ($field == Client::$fieldVat_number && !$client->vat_number)
				{
					$client->vat_number = $value;
				}
				else if ($field == Client::$fieldName && !$client->name)
				{
					$client->name = $value;
				}
				else if ($field == Client::$fieldNit && !$client->nit)
				{
					$client->nit = $value;
				}
				else if ($field == Client::$fieldPhone && !$client->work_phone)
				{
					$client->work_phone = $value;
				}
				else if ($field == Client::$fieldAddress1 && !$client->address1)
				{
					$client->address1 = $value;
				}
				else if ($field == Client::$fieldAddress2 && !$client->address2)
				{
					$client->address2 = $value;
				}
				else if ($field == Client::$fieldNotes && !$client->private_notes)
				{
					$client->private_notes = $value;
				}


				else if ($field == Contact::$fieldFirstName && !$contact->first_name)
				{
					$contact->first_name = $value;
				}
				else if ($field == Contact::$fieldLastName && !$contact->last_name)
				{
					$contact->last_name = $value;
				}
				else if ($field == Contact::$fieldPhone && !$contact->phone)
				{
					$contact->phone = $value;
				}
				else if ($field == Contact::$fieldEmail && !$contact->email)
				{
					$contact->email = strtolower($value);
				}				
			}

			$client->save();
			$client->contacts()->save($contact);	
			$client->save();	

			Activity::createClient($client, false);
		}

		$message = Utils::pluralize('created_client', $count);
		Session::flash('message', $message);
		return Redirect::to('clients');
	}

	private function importFilei()
	{
		$data = Session::get('data');

		$map = Input::get('map');
		$count = 0;

		$hasHeaders = true;
		
		foreach ($data as $row)
		{
			if ($hasHeaders)
			{
				$hasHeaders = false;
				continue;
			}
			foreach ($row as $index => $value)
			{
				$field = $map[$index];
				$value = trim($value);

			    if ($field == Invoice::$fieldCodClient)
				{
					
					$clients = Client::scope()->get();
					
					$flag = 1;

					foreach ($clients as $client) 
					{	
						$cod_client = intval($value);

						if($client->public_id==$cod_client)
						{
							$flag = 1;
						}
					}
						if($flag == 0)
						{
							$message = 'cliente no encontrado ' . $value . 'Favor revisar el archivo importado ';
							Session::flash('message', $message);
							return Redirect::to('company/import_export');
						}

				}

				else if ($field == Invoice::$fieldProduct)
				{
					if($value == '')
					{
						$message = 'Concepto vacío. Favor revisar el archivo importado ';
						Session::flash('message', $message);
						return Redirect::to('company/import_export');
					}
				}

				else if ($field == Invoice::$fieldAmount)
				{
			        if($value == '')
					{
						$message = 'Monto vacío. Favor revisar el archivo importado ';
						Session::flash('message', $message);
						return Redirect::to('company/import_export');
					}
				}
			
			}
		}





		$branch_id = Input::get('branch_id');
		$clients = Client::scope()->get();

		$clientMap = [];

		$data = Session::get('data');

		Session::forget('data');

		$hasHeaders = true;
	
		foreach ($clients as $client) 
		{	
			$i = 0;
			$clientMap[$client->public_id][$i] = $client->id;
			$clientMap[$client->public_id][$i+1] = $client->nit;
			$clientMap[$client->public_id][$i+2] = $client->name;
		}		

		foreach ($data as $row)
		{
			if ($hasHeaders)
			{
				$hasHeaders = false;
				continue;
			}

			$invoice = Invoice::createNew();
    		$invoiceItem = InvoiceItem::createNew();
			
			$count++;

			$branch = \DB::table('branches')->where('id',$branch_id)->first();

	        $invoice->branch_id = $branch_id;
	        $invoiceNumber = $branch->invoice_number_counter;
	        $invoice->invoice_number = $invoiceNumber;
		    $today = new DateTime('now');
		    $invoice->invoice_date = $today->format('Y-m-d');

		    $invoiceDesign = \DB::table('invoice_designs')->where('account_id',\Auth::user()->account_id)->orderBy('public_id', 'desc')->first();
		    $invoice->invoice_design_id = $invoiceDesign->id;

		    $account = \Auth::user()->account;

		    $invoice->account_name=$account->name;
			$invoice->account_nit=$account->nit;

			$invoice->branch_name=$branch->name;
			$invoice->address1=$branch->address1;
			$invoice->address2=$branch->address2;

			$invoice->phone=$branch->postal_code;
			$invoice->city=$branch->city;
			$invoice->state=$branch->state;

			$invoice->number_autho=$branch->number_autho;
			$invoice->deadline=$branch->deadline;
			$invoice->key_dosage=$branch->key_dosage;
			$invoice->activity_pri=$branch->activity_pri;
			$invoice->activity_sec1=$branch->activity_sec1;
	        $invoice->law=$branch->law;

			foreach ($row as $index => $value)
			{
				$field = $map[$index];
				$value = trim($value);


			    if ($field == Invoice::$fieldCodClient)
				{
					$cod_client = intval($value);
					$invoice->client_id = $clientMap[$cod_client][0];
					$invoice->client_nit = $clientMap[$cod_client][1];
					$invoice->client_name = $clientMap[$cod_client][2];
				}

				else if ($field == Invoice::$fieldProduct)
				{
					$notes = $value;
				}

				else if ($field == Invoice::$fieldAmount)
				{

			        $invoiceItem->notes = wordwrap($notes, 70, "\n");
 
					$str = str_replace(".", "", $value);  
					$amount = str_replace(",", ".", $str);  
			        $invoiceItem->cost = $amount;
			        $invoiceItem->qty = 1;
			        $invoiceItem->tax_rate = 0;
			        $invoice->subtotal = $invoiceItem->cost;
					$invoice->amount = $invoiceItem->cost;
					$invoice->balance = $invoiceItem->cost; 
				}
			}

			$invoice_dateCC = date("Ymd", strtotime($invoice->invoice_date));
	        $invoice_date_limitCC = date("d/m/Y", strtotime($branch->deadline));

	        require_once(app_path().'/includes/control_code.php');
	        $cod_control = codigoControl($invoice->invoice_number, $invoice->client_nit, $invoice_dateCC, $invoice->amount, $branch->number_autho, $branch->key_dosage);

	        $invoice->control_code=$cod_control;

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
			$invoice->invoice_items()->save($invoiceItem);		

			// Activity::createInvoice($invoice, false);
		}

		$message = Utils::pluralize('created_invoicei', $count);
		Session::flash('message', $message);
		return Redirect::to('invoices');
	}

	private function mapFile()
	{		
		$file = Input::file('file');

		if ($file == null)
		{
			Session::flash('error', trans('texts.select_file'));
			return Redirect::to('company/import_export');			
		}

		$name = $file->getRealPath();

		require_once(app_path().'/includes/parsecsv.lib.php');
		$csv = new parseCSV();
		$csv->heading = false;
		$csv->auto($name);
		
		if (count($csv->data) + Client::scope()->count() > Auth::user()->getMaxNumClients())
		{
      $message = trans('texts.limit_clients', ['count' => Auth::user()->getMaxNumClients()]);
			Session::flash('error', $message);
			return Redirect::to('company/import_export');
		}

		Session::put('data', $csv->data);

		$headers = false;
		$hasHeaders = false;
		$mapped = array();
		$columns = array('',
			
			Client::$fieldVat_number,

			Client::$fieldName,
			Client::$fieldNit,

			Client::$fieldPhone,
			Client::$fieldAddress1,
			Client::$fieldAddress2,

			Client::$fieldNotes,

			Contact::$fieldFirstName,
			Contact::$fieldLastName,
			Contact::$fieldPhone,
			Contact::$fieldEmail

		);

		if (count($csv->data) > 0) 
		{
			$headers = $csv->data[0];
			foreach ($headers as $title) 
			{
				if (strpos(strtolower($title),'name') > 0)
				{
					$hasHeaders = true;
					break;
				}
			}

			for ($i=0; $i<count($headers); $i++)
			{
				$title = strtolower($headers[$i]);
				$mapped[$i] = '';

				if ($hasHeaders)
				{
					$map = array(

						'Nombre' => Client::$fieldVat_number,

						'Razón Social' => Client::$fieldName,
						'Nit' => Client::$fieldNit,

						'Teléfono' => Client::$fieldPhone,
						'Zona/Barrio' => Client::$fieldAddress1,	
						'Dirección' => Client::$fieldAddress2,						
						'Antecedentes' => Client::$fieldNotes,

						'Nombre(s)' => Contact::$fieldFirstName,
						'Apellidos' => Contact::$fieldLastName,
						'Correo' => Contact::$fieldEmail,
						'Celular' => Contact::$fieldPhone,
						
					);

					foreach ($map as $search => $column)
					{
						foreach(explode("|", $search) as $string)
						{
							if (strpos($title, 'sec') === 0)
							{
								continue;
							}

							if (strpos($title, $string) !== false)
							{
								$mapped[$i] = $column;
								break(2);
							}
						}
					}
				}
			}
		}

		$data = array(
			'data' => $csv->data, 
			'headers' => $headers,
			'hasHeaders' => $hasHeaders,
			'columns' => $columns,
			'mapped' => $mapped
		);

		return View::make('accounts.import_map', $data);
	}

	private function mapFileInvoice()
	{		
		$file = Input::file('file');

		if ($file == null)
		{
			Session::flash('error', trans('texts.select_file'));
			return Redirect::to('company/import_export');			
		}

		$name = $file->getRealPath();

		require_once(app_path().'/includes/parsecsv.lib.php');
		$csv = new parseCSV();
		$csv->heading = false;
		$csv->auto($name);

		Session::put('data', $csv->data);

		$headers = false;
		$hasHeaders = false;
		$mapped = array();
		$columns = array('',

			Invoice::$fieldCodClient,	
			Invoice::$fieldProduct,	
			Invoice::$fieldAmount

		);

		if (count($csv->data) > 0) 
		{
			$headers = $csv->data[0];
			foreach ($headers as $title) 
			{
				if (strpos(strtolower($title),'name') > 0)
				{
					$hasHeaders = true;
					break;
				}
			}

			for ($i=0; $i<count($headers); $i++)
			{
				$title = strtolower($headers[$i]);
				$mapped[$i] = '';

				if ($hasHeaders)
				{
					$map = array(

						'cod_client' => Invoice::$fieldCodClient,
						'product' => Invoice::$fieldProduct,
						'amount' => Invoice::$fieldAmount,

					);

					foreach ($map as $search => $column)
					{
						foreach(explode("|", $search) as $string)
						{
							if (strpos($title, 'sec') === 0)
							{
								continue;
							}

							if (strpos($title, $string) !== false)
							{
								$mapped[$i] = $column;
								break(2);
							}
						}
					}
				}
			}
		}

		$data = array(
			'data' => $csv->data, 
			'headers' => $headers,
			'hasHeaders' => $hasHeaders,
			'columns' => $columns,
			'mapped' => $mapped

		);
		$data['branches'] = Branch::where('account_id', '=', Auth::user()->account_id)->orderBy('id')->get();

		return View::make('accounts.import_map_invoice', $data);
	}

	private function saveNotifications()
	{
		$account = Auth::user()->account;
		$account->invoice_terms = Input::get('invoice_terms');
		$account->email_footer = Input::get('email_footer');
		$account->save();

		$user = Auth::user();
		$user->notify_sent = Input::get('notify_sent');
		$user->notify_viewed = Input::get('notify_viewed');
		$user->notify_paid = Input::get('notify_paid');
		$user->save();
		
		Session::flash('message', trans('texts.updated_settings'));
		return Redirect::to('company/notifications');
	}

	private function savePayments()
	{  
		$rules = array();
		$recommendedId = Input::get('recommendedGateway_id');

		if ($gatewayId = $recommendedId == 1000000 ? Input::get('gateway_id') : $recommendedId) 
		{
			$gateway = Gateway::findOrFail($gatewayId);
			
			$paymentLibrary = $gateway->paymentlibrary;
			
			$fields = $gateway->getFields();
			
			foreach ($fields as $field => $details)
			{
				if (!in_array($field, ['testMode', 'developerMode', 'headerImageUrl', 'solutionType', 'landingPage', 'brandName']))
				{
					if(strtolower($gateway->name) == 'beanstream')
					{
						if(in_array($field, ['merchant_id', 'passCode']))
						{
							$rules[$gateway->id.'_'.$field] = 'required';
						}
					} 
					else 
					{
						$rules[$gateway->id.'_'.$field] = 'required';
					}
				}				
			}			
		}
        
    $creditcards = Input::get('creditCardTypes');
		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) 
		{
			return Redirect::to('company/payments')
				->withErrors($validator)
				->withInput();
		} 
		else 
		{
			$account = Account::with('account_gateways')->findOrFail(Auth::user()->account_id);						

			if ($gatewayId) 
			{
				$accountGateway = AccountGateway::createNew();
				$accountGateway->gateway_id = $gatewayId;
				$isMasked = false;

				$config = new stdClass;
				foreach ($fields as $field => $details)
				{
					$value = trim(Input::get($gateway->id.'_'.$field));

					if ($value && $value === str_repeat('*', strlen($value)))
					{
						$isMasked = true;
					}

					$config->$field = $value;
				}
                
        $cardCount = 0;
        if ($creditcards) 
        {
	        foreach($creditcards as $card => $value)
	        {
            $cardCount += intval($value);
	        }			
        }
				
				if ($isMasked && count($account->account_gateways)) 
				{
					$currentGateway = $account->account_gateways[0];
					$currentGateway->accepted_credit_cards = $cardCount;
					$currentGateway->save();
				} 
				else 
				{
					$accountGateway->config = json_encode($config);
					$accountGateway->accepted_credit_cards = $cardCount;
	
					$account->account_gateways()->delete();
					$account->account_gateways()->save($accountGateway);
				}

				Session::flash('message', trans('texts.updated_settings'));
			}
			else
			{
				Session::flash('error', trans('validation.required', ['attribute' => 'gateway']));
			}
			
			return Redirect::to('company/payments');
		}				
	}

	private function saveDetails()
	{


		$user = Auth::user()->account->users()->first();

		if (Auth::user()->id === $user->id)		
		{
			$rules['email'] = 'email|required|unique:users,email,' . $user->id . ',id';
		}

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) 
		{
			return Redirect::to('company/details')
				->withErrors($validator)
				->withInput();
		} 
		else 
		{
			$account = Auth::user()->account;
			
			if(!Auth::user()->isPro())
			{
				$account->nit= trim(Input::get('nit'));
				$account->name = trim(Input::get('name'));
			}

			$account->work_phone = trim(Input::get('work_phone'));
			$account->address1 = trim(Input::get('address1'));
			$account->address2 = trim(Input::get('address2'));
			$account->currency_id = 1;
			$account->language_id = 1;
			$account->op1 = true;

            if(Input::get('unipersonal'))
            {
		        $account->is_uniper = 1;
		        $account->uniper = Input::get('uniper');
            }
            else
            {
            	$account->is_uniper = 0;
            	$account->uniper = '';
            }

			$account->save();

			if (Auth::user()->id === $user->id)
			{
				$user->first_name = trim(Input::get('first_name'));
				$user->last_name = trim(Input::get('last_name'));
				$user->password = trim(Input::get('password'));
				$user->password_confirmation = trim(Input::get('password_confirmation'));
				
				if(Input::get('nit'))
				{
					$user->username = trim(Input::get('username'))."@".trim(Input::get('nit'));
				}else
				{
           			$user->username = trim(Input::get('username'))."@".Auth::user()->account->getNit();      
				}

				$user->email = trim(strtolower(Input::get('email')));
				$user->phone = trim(Input::get('phone'));				
				$user->save();
			}

			Event::fire('user.refresh');

			if (Auth::user()->confirmed)
			{
			Session::flash('message', trans('texts.updated_settings'));
			return Redirect::to('company/details');
			}
			else
			{
				Session::flash('message', trans('texts.updated_settings'));
				return Redirect::to('company/branches');	
			}
		}
	}

	public function checkEmail()
	{		
		$email = User::withTrashed()->where('email', '=', Input::get('email'))->where('id', '<>', Auth::user()->id)->first();

		if ($email) 
		{
			return "taken";
		} 
		else 
		{
			return "available";
		}
	}

	public function submitSignup()
	{
		$rules = array(
			'new_nit' => 'required',
			'new_name' => 'required',
			'new_first_name' => 'required',
			'new_last_name' => 'required',
			'new_username' => 'required',
			'new_password' => 'required|min:6',
			'new_email' => 'email|required|unique:users,email,' . Auth::user()->id . ',id'
		);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) 
		{
			return '';
		} 

		$user = Auth::user();
		$user->first_name = trim(Input::get('new_first_name'));
		$user->last_name = trim(Input::get('new_last_name'));
		$user->email = trim(strtolower(Input::get('new_email')));
		$user->username = trim(Input::get('new_username')).'@'.trim(Input::get('new_nit'));
		$user->password = trim(Input::get('new_password'));
		$user->password_confirmation = trim(Input::get('new_password'));
		$user->amend();

		// $this->userMailer->sendConfirmation($user);

		$account = Auth::user()->account;

		$account->nit= trim(Input::get('new_nit'));
		$account->name = trim(Input::get('new_name'));
		$account->save();

		$activities = Activity::scope()->get();
		foreach ($activities as $activity) 
		{
			$activity->message = str_replace('Cliente de FV', $user->getFullName(), $activity->message);
			$activity->save();
		}

		if (Input::get('go_pro') == 'true')
		{
			Session::set(REQUESTED_PRO_PLAN, true);
		}

		Session::set(SESSION_COUNTER, -1);

		return "{$user->first_name} {$user->last_name}";
	}

}