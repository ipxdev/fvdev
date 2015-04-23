<?php

use ninja\repositories\ClientRepository;

class ClientController extends \BaseController {

	protected $clientRepo;

	public function __construct(ClientRepository $clientRepo)
	{
		parent::__construct();

		$this->clientRepo = $clientRepo;
	}	

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		return View::make('list', array(
			'entityType'=>ENTITY_CLIENT, 
			'title' => trans('texts.clients'),
			'columns'=>Utils::trans(['checkbox','cod', 'vat_number', 'contact', 'work_phone', 'balance', 'paid_to_dat', 'action'])
		));		
	}

	public function getDatatable()
    {    	
    	$clients = $this->clientRepo->find(Input::get('sSearch'));

        if (Utils::isAdmin())
        {
        return Datatable::query($clients)
    	    ->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; })
            ->addColumn('public_id', function($model) {  return $model->public_id; })
    	    ->addColumn('vat_number', function($model) { return link_to('clients/' . $model->public_id, $model->vat_number); })
    	    ->addColumn('first_name', function($model) { return $model->first_name . ' ' . $model->last_name; })
    	    ->addColumn('work_phone', function($model) { return $model->work_phone ? $model->work_phone : $model->phone; })
    	    ->addColumn('balance', function($model) { return Utils::formatMoney($model->balance, $model->currency_id); })    	    
    	    ->addColumn('paid_to_date', function($model) { return Utils::formatMoney($model->paid_to_date, $model->currency_id); })    	    
    	    ->addColumn('dropdown', function($model) 
    	    { 
    	    	return '<div class="btn-group tr-action" style="visibility:hidden;">
  							<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
    							'.trans('texts.select').' <span class="caret"></span>
  							</button>
  							<ul class="dropdown-menu" role="menu">
  							<li><a href="' . URL::to('clients/'.$model->public_id.'/edit') . '">'.trans('texts.edit_client').'</a></li>
						    <li class="divider"></li>
						    <li><a href="' . URL::to('invoices/create/'.$model->public_id) . '">'.trans('texts.new_invoice').'</a></li>						    
						    <li><a href="' . URL::to('payments/create/'.$model->public_id) . '">'.trans('texts.new_payment').'</a></li>						    
						    <li><a href="' . URL::to('credits/create/'.$model->public_id) . '">'.trans('texts.new_credit').'</a></li>						    
						    <li class="divider"></li>
						    <li><a href="javascript:archiveEntity(' . $model->public_id. ')">'.trans('texts.archive_client').'</a></li>
						  </ul>
						</div>';
    	    })    	   
    	    ->make();
        }
    	else
    	{
    	    return Datatable::query($clients)
    	    ->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; })
    	    ->addColumn('public_id', function($model) {  return $model->public_id; })
    	    ->addColumn('vat_number', function($model) { return link_to('clients/' . $model->public_id, $model->vat_number); })
    	    ->addColumn('first_name', function($model) { return link_to('clients/' . $model->public_id, $model->first_name . ' ' . $model->last_name); })
    	    ->addColumn('work_phone', function($model) { return $model->work_phone ? $model->work_phone : $model->phone; })
    	    ->addColumn('balance', function($model) { return Utils::formatMoney($model->balance, $model->currency_id); })    	    
    	    ->addColumn('paid_to_date', function($model) { return Utils::formatMoney($model->paid_to_date, $model->currency_id); })    	    
    	    ->addColumn('dropdown', function($model) 
    	    { 
    	    	return '<div class="btn-group tr-action" style="visibility:hidden;">
  							<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
    							'.trans('texts.select').' <span class="caret"></span>
  							</button>
  							<ul class="dropdown-menu" role="menu">
						    <li><a href="' . URL::to('invoices/create/'.$model->public_id) . '">'.trans('texts.new_invoice').'</a></li>						    
						    <li><a href="' . URL::to('payments/create/'.$model->public_id) . '">'.trans('texts.new_payment').'</a></li>						    
						    <li><a href="' . URL::to('credits/create/'.$model->public_id) . '">'.trans('texts.new_credit').'</a></li>						    
						  </ul>
						</div>';
    	    })    	   
    	    ->make();  
    	}

    }



	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		return $this->save();
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($publicId)
	{
		$client = Client::withTrashed()->scope($publicId)->with('contacts', 'size', 'industry')->firstOrFail();
		Utils::trackViewed($client->getDisplayName(), ENTITY_CLIENT);
	
		$actionLinks = [
			[trans('texts.create_invoice'), URL::to('invoices/create/' . $client->public_id )],
     	[trans('texts.enter_payment'), URL::to('payments/create/' . $client->public_id )],
     	[trans('texts.enter_credit'), URL::to('credits/create/' . $client->public_id )]
    ];

    if (Utils::isPro())
    {
    	// array_unshift($actionLinks, [trans('texts.create_quote'), URL::to('quotes/create/' . $client->public_id )]);
    }

		$data = array(
			'actionLinks' => $actionLinks,
			'showBreadcrumbs' => false,
			'client' => $client,
			'credit' => $client->getTotalCredit(),
			'title' => trans('texts.view_client'),
			'hasRecurringInvoices' => Invoice::scope()->where('is_recurring', '=', true)->whereClientId($client->id)->count() > 0
		);

		return View::make('clients.show', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{		
		if (Client::scope()->count() > Auth::user()->getMaxNumClients())
		{
			return View::make('error', ['hideHeader' => true, 'error' => "Lo sentimos, se ha superado el límite de " . Auth::user()->getMaxNumClients() . " clients"]);
		}

		$data = [
			'client' => null, 
			'method' => 'POST', 
			'url' => 'clients', 
			'title' => trans('texts.new_client')
		];

		$data = array_merge($data, self::getViewModel());	
		return View::make('clients.edit', $data);
	}	

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($publicId)
	{
		$client = Client::scope($publicId)->with('contacts')->firstOrFail();
		$data = [
			'client' => $client, 
			'method' => 'PUT', 
			'url' => 'clients/' . $publicId, 
			'title' => trans('texts.edit_client')
		];

		$data = array_merge($data, self::getViewModel());			
		return View::make('clients.edit', $data);
	}

	private static function getViewModel()
	{
		return [		
			'sizes' => Size::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
			'paymentTerms' => PaymentTerm::remember(DEFAULT_QUERY_CACHE)->orderBy('num_days')->get(['name', 'num_days']),
			'industries' => Industry::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
			'customLabel1' => Auth::user()->account->custom_client_label1,
			'customLabel2' => Auth::user()->account->custom_client_label2,
			'customLabel3' => Auth::user()->account->custom_client_label3,
			'customLabel4' => Auth::user()->account->custom_client_label4,
			'customLabel5' => Auth::user()->account->custom_client_label5,
			'customLabel6' => Auth::user()->account->custom_client_label6,
			'customLabel7' => Auth::user()->account->custom_client_label7,
			'customLabel8' => Auth::user()->account->custom_client_label8,
			'customLabel9' => Auth::user()->account->custom_client_label9,
			'customLabel10' => Auth::user()->account->custom_client_label10,
			'customLabel11' => Auth::user()->account->custom_client_label11,
			'customLabel12' => Auth::user()->account->custom_client_label12,
		];
	}	

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($publicId)
	{
		return $this->save($publicId);
	}

	private function save($publicId = null)
	{
		$rules = array(
			
			'nit' => 'required'

		);
		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) 
		{
			$url = $publicId ? 'clients/' . $publicId . '/edit' : 'clients/create';
			return Redirect::to($url)
				->withErrors($validator)
				->withInput(Input::except('password'));
		} 
		else 
		{			
			if ($publicId) 
			{
				$client = Client::scope($publicId)->firstOrFail();
			} 
			else 
			{
				$client = Client::createNew();
			}
			$client->nit = trim(Input::get('nit'));
			$client->name = trim(Input::get('name'));
            $client->vat_number = trim(Input::get('vat_number'));
			$client->work_phone = trim(Input::get('work_phone'));

			$client->custom_value1 = trim(Input::get('custom_value1'));
			$client->custom_value2 = trim(Input::get('custom_value2'));
			$client->custom_value3 = trim(Input::get('custom_value3'));
			$client->custom_value4 = trim(Input::get('custom_value4'));
			$client->custom_value5 = trim(Input::get('custom_value5'));
			$client->custom_value6 = trim(Input::get('custom_value6'));
			$client->custom_value7 = trim(Input::get('custom_value7'));
			$client->custom_value8 = trim(Input::get('custom_value8'));
			$client->custom_value9 = trim(Input::get('custom_value9'));
			$client->custom_value10 = trim(Input::get('custom_value10'));
			$client->custom_value11 = trim(Input::get('custom_value11'));
			$client->custom_value12 = trim(Input::get('custom_value12'));

			$client->address1 = trim(Input::get('address1'));
			$client->address2 = trim(Input::get('address2'));
			$client->city = trim(Input::get('city'));
			$client->state = trim(Input::get('state'));
			$client->postal_code = trim(Input::get('postal_code'));			
			$client->country_id = Input::get('country_id') ? : null;
			$client->private_notes = trim(Input::get('private_notes'));
			$client->size_id = Input::get('size_id') ? : null;
			$client->industry_id = Input::get('industry_id') ? : null;
			$client->currency_id = Input::get('currency_id') ? : 1;
			$client->payment_terms = Input::get('payment_terms') ? : 0;
			$client->website = trim(Input::get('website'));

			$client->save();

			$data = json_decode(Input::get('data'));
			$contactIds = [];
			$isPrimary = true;
			
			foreach ($data->contacts as $contact)
			{
				if (isset($contact->public_id) && $contact->public_id)
				{
					$record = Contact::scope($contact->public_id)->firstOrFail();
				}
				else
				{
					$record = Contact::createNew();
				}

				$record->email = trim(strtolower($contact->email));
				$record->first_name = trim($contact->first_name);
				$record->last_name = trim($contact->last_name);
				$record->phone = trim($contact->phone);
				$record->aux1 = trim($contact->aux1);
				$record->aux2 = trim($contact->aux2);
				$record->is_primary = $isPrimary;
				$isPrimary = false;

				$client->contacts()->save($record);
				$contactIds[] = $record->public_id;					
			}

			foreach ($client->contacts as $contact)
			{
				if (!in_array($contact->public_id, $contactIds))
				{	
					$contact->delete();
				}
			}
						
			if ($publicId) 
			{
				Session::flash('message', trans('texts.updated_client'));
			} 
			else 
			{
				Activity::createClient($client);
				Session::flash('message', trans('texts.created_client'));
			}

			return Redirect::to('clients/' . $client->public_id);
		}
	}

	public function bulk()
	{
		$action = Input::get('action');
		$ids = Input::get('id') ? Input::get('id') : Input::get('ids');		
		$count = $this->clientRepo->bulk($ids, $action);

		$message = Utils::pluralize($action.'d_client', $count);
		Session::flash('message', $message);

		return Redirect::to('clients');
	}
	//modulos david
	public function cliente($public_id)
    {
     	$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id')->where('id',$user_id)->first();
    	$client =  DB::table('clients')->select('id','name','nit','vat_number')->where('account_id',$user->account_id)->where('public_id',$public_id)->first();
    	
    	if($client!=null)
    	{

    		$datos = array(
    			'resultado' => 0,
    			'cliente' => $client

    		);
    		return Response::json($datos);	
    	}
    	$datos = array(
    			'resultado' => 1,
    			'mensaje' => 'cliente no encontrado'

    		);
    		return Response::json($datos);	
    	
    }
     public function obtenerFactura($public_id)
    {
    		$user_id = Auth::user()->getAuthIdentifier();
    	$user = DB::table('users')->select('account_id','branch_id')->where('id',$user_id)->first();
    	$client =  DB::table('clients')->select('id','name','nit','public_id','custom_value4')->where('account_id',$user->account_id)->where('public_id',$public_id)->first();
    	
    	if($client==null)
    	{
				$datos = array(
    			'resultado' => 1,
    			'mensaje' => 'cliente no encontrado'

    		);
    		return Response::json($datos);	
    	}
    	

    	//caso contrario tratar al cliente
    	$branch = Auth::user()->branch;
    	$invoices = DB::table('invoices')
					 // ->join('clients', 'clients.id', '=', 'invoices.client_id')
					 // ->where('account_id','=',$user->account_id)
					 ->where('branch_id','=',$user->branch_id)
					 ->where('client_id','=',$client->id)
					 // -where('')
					 ->orderBy('invoice_number')
					 // ->first();
					 // ->get();
					 ->get(array('id','invoice_number'));

		if($invoices==null)
    	{
				$datos = array(
    			'resultado' => 2,
    			'mensaje' => 'Cliente no emitio ninguna factura'

    		);
    		return Response::json($datos);	
    	}

		$inv=""; 
		foreach ($invoices as $invo) 
    	{
    		$inv = $invo;
		}	
		$invoice = DB::table('invoices')
				   ->where('id','=',$inv->id)
				   ->first(); 
		// $invoiceItems = DB::table('invoice_items')
		// 				->where('invoice_id','=',$inv->id)
		// 				->get(array('notes','cost','qty','boni','desc'));  
						
						// if(json.has("notes"))
      //       {
      //           it.setNotes(json.getString("notes"));
      //       }
      //       if(json.has("cost"))
      //       {
      //           it.setCost(json.getString("cost"));
      //       }
      //       if(json.has("qty"))
      //       {
      //           it.setQty(json.getString("qty"));
      //       }
      //       if(json.has("boni"))
      //       {
      //           it.setBoni(json.getString("boni"));
      //       }
      //       if(json.has("desc"))
      //       {
      //           it.setDesc(json.getString("desc"));
      //       } 
	// $factura  = array('invoice_number' => $invoice->invoice_number,
 //    					'control_code'=>$invoice->control_code,
 //    					'invoice_date'=>$invoice->invoice_date,
 //    					'amount'=>$invoice->amount,
 //    					'subtotal'=>$invoice->subtotal,
 //    					'fiscal'=>$invoice->fiscal,
 //    					'client'=>$client,

 //    					'account'=>$account,
 //    					'law' => $invoice->law,
 //    					'invoice_items'=>$invoiceItems,
 //    					'address1'=>$invoice->address1,
 //    					'address2'=>$invoice->address2,
 //    					'num_auto'=>$invoice->number_autho,
 //    					'fecha_limite'=>$invoice->deadline,
 //    					'custom_value1'=>$invoice->custom_value1	
    					
 //    					);
// return Response::json($invoice);

		$invoiceItems =DB::table('invoice_items')
    				   ->select('notes','cost','qty')
    				   ->where('invoice_id','=',$invoice->id)
    				   ->get(array('notes','cost','qty'));

    	// $date = date_create($invoice->deadline);
    				   $date = new DateTime($invoice->deadline);
    	// $account = DB::table('accounts')->select('name','nit')->where('id',$user->account_id)->first();
    	$account  = array('name' =>$invoice->account_name,'nit'=>$invoice->account_nit );
    	$client->name = $invoice->client_name;
    	$client->nit = $invoice->client_nit;
		$ice = $invoice->amount-$invoice->fiscal;
		$cliente  = array('name' => $invoice->client_name ,'nit'=>$invoice->client_nit);
		$factura  = array(
						'resultado' => 0,
						'activity_pri'=>$invoice->activity_pri,
						'invoice_number' => $invoice->invoice_number,
    					'control_code'=>$invoice->control_code,
    					'invoice_date'=>$invoice->invoice_date,
    					'amount'=>$invoice->amount,
    					'subtotal'=>$invoice->subtotal,
    					'fiscal'=>$invoice->fiscal,
    					'client'=>$client,

    					'account'=>$account,
    					'law' => $invoice->law,

    					'invoice_items'=>$invoiceItems,
    					'address1'=>$invoice->address1,
    					'address2'=>$invoice->address2,
    					'num_auto'=>$invoice->number_autho,
    					'fecha_limite'=>$date->format('d-m-Y'),
    					// 'ice'=>$ice	
    					);
		//its work ok go  get the money
		return Response::json($factura);			 
					 


    }
}