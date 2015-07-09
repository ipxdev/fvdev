<?php namespace ninja\repositories;

use Invoice;
use InvoiceItem;
use Invitation;
use Product;
use Utils;
use TaxRate;
use BookSale;

class InvoiceRepository
{
	public function getInvoices($accountId, $clientPublicId = false, $filter = false)
	{
    	$query = \DB::table('invoices')
    				->join('clients', 'clients.id', '=','invoices.client_id')
  					->join('invoice_statuses', 'invoice_statuses.id', '=', 'invoices.invoice_status_id')
  					->join('contacts', 'contacts.client_id', '=', 'clients.id')
            ->join('branches', 'branches.id', '=','invoices.branch_id')
            ->where('invoices.account_id', '=', $accountId)
    				->where('invoices.is_recurring', '=', false)
            ->where('contacts.deleted_at', '=', null)    			
    				->where('contacts.is_primary', '=', true)	
  					->select('clients.public_id as client_public_id', 'invoice_number', 'invoice_status_id', 'clients.name as client_name', 'branches.name as branch_name', 'invoices.public_id', 'amount', 'invoices.balance', 'invoice_date', 'due_date', 'invoice_statuses.name as invoice_status_name', 'contacts.first_name', 'contacts.last_name', 'contacts.email', 'quote_id', 'quote_invoice_id');

      if (!\Session::get('show_trash:invoice'))
      {
        $query->where('invoices.deleted_at', '=', null);
      }

    	if ($clientPublicId) 
    	{
    		$query->where('clients.public_id', '=', $clientPublicId);
    	}

    	if ($filter)
    	{
    		$query->where(function($query) use ($filter)
            {
            	$query->where('clients.name', 'like', '%'.$filter.'%')
            		  ->orWhere('invoices.invoice_number', 'like', '%'.$filter.'%')
            		  ->orWhere('invoice_statuses.name', 'like', '%'.$filter.'%')
                  ->orWhere('contacts.first_name', 'like', '%'.$filter.'%')
                  ->orWhere('contacts.last_name', 'like', '%'.$filter.'%')
                  ->orWhere('contacts.email', 'like', '%'.$filter.'%');
            });
    	}

    	return $query;
	}

	public function getRecurringInvoices($accountId, $clientPublicId = false, $filter = false)
	{
    	$query = \DB::table('invoices')
    				->join('clients', 'clients.id', '=','invoices.client_id')
  					->join('frequencies', 'frequencies.id', '=', 'invoices.frequency_id')
	   				->join('contacts', 'contacts.client_id', '=', 'clients.id')
		  			->where('invoices.account_id', '=', $accountId)
            ->where('invoices.is_quote', '=', false)
            ->where('clients.deleted_at', '=', null)
    				->where('invoices.is_recurring', '=', true)
    				->where('contacts.is_primary', '=', true)	
			   		->select('clients.public_id as client_public_id', 'clients.name as client_name', 'invoices.public_id', 'amount', 'frequencies.name as frequency', 'start_date', 'end_date', 'contacts.first_name', 'contacts.last_name', 'contacts.email');

    	if ($clientPublicId) 
    	{
    		$query->where('clients.public_id', '=', $clientPublicId);
    	}
      
      if (!\Session::get('show_trash:invoice'))
      {
        $query->where('invoices.deleted_at', '=', null);
      }

    	if ($filter)
    	{
    		$query->where(function($query) use ($filter)
            {
            	$query->where('clients.name', 'like', '%'.$filter.'%')
            		  ->orWhere('invoices.invoice_number', 'like', '%'.$filter.'%');
            });
    	}

    	return $query;
	}

  public function getDatatable($accountId, $clientPublicId = null, $entityType, $search)
  {
    $query = $this->getInvoices($accountId, $clientPublicId, $search)
              ->where('invoices.is_quote', '=', $entityType == ENTITY_QUOTE ? true : false);

    $table = \Datatable::query($query);      

    if (!$clientPublicId) 
    {
      $table->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; });
    }

    $table->addColumn("invoice_number", function($model) use ($entityType) { return link_to("{$entityType}s/" . $model->public_id . '/edit', $model->invoice_number); });

    if (!$clientPublicId) 
    {
      $table->addColumn('client_name', function($model) { return link_to('clients/' . $model->client_public_id, Utils::getClientDisplayName($model)); });
    }

    $table->addColumn("invoice_date", function($model) { return Utils::fromSqlDate2($model->invoice_date); });        

    $table->addColumn("branch_name", function($model) { return $model->branch_name; });        

    $table->addColumn('amount', function($model) { return Utils::formatMoney($model->amount, $model->currency_id); });

    if ($entityType == ENTITY_INVOICE)
    {
      $table->addColumn('balance', function($model) { return Utils::formatMoney($model->balance, $model->currency_id); });
    }

    return $table->addColumn('due_date', function ($model) { return Utils::fromSqlDate2($model->due_date); })
    ->addColumn('invoice_status_name', function($model) { return $model->invoice_status_name; })
        ->addColumn('dropdown', function($model) use ($entityType)
        { 
          $str = '<div class="btn-group tr-action" style="visibility:hidden;">
              <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
              <span class="caret"></span>
              </button>
              <ul class="dropdown-menu" role="menu">
              <li><a href="' . \URL::to("{$entityType}s/".$model->public_id.'/edit') . '">'.trans("texts.edit_{$entityType}").'</a></li>';           
                      
            if ($entityType == ENTITY_INVOICE)              
            {
              if ($model->invoice_status_id < INVOICE_STATUS_PAID)
              {
                $str .= '<li class="divider"></li><li><a href="' . \URL::to('payments/create/' . $model->client_public_id . '/' . $model->public_id ) . '">'.trans('texts.enter_payment').'</a></li>';
              }

              if ($model->quote_id)
              {
                $str .= '<li><a href="' .  \URL::to("quotes/{$model->quote_id}/edit") . '">' . trans("texts.view_quote") . '</a></li>';
              }            
            }
            else if ($entityType == ENTITY_QUOTE)
            {
              if ($model->quote_invoice_id)
              {
                $str .= '<li><a href="' .  \URL::to("invoices/{$model->quote_invoice_id}/edit") . '">' . trans("texts.view_invoice") . '</a></li>';
              }
            }

            return $str . '<li class="divider"></li>
                <li><a href="javascript:deleteEntity(' . $model->public_id . ')">'.trans("texts.delete_{$entityType}").'</a></li>               
              </ul>
            </div>';
          })                  
          ->make();
  }


	public function getErrors($input)
	{
		// $contact = (array) $input->client->contacts[0];
		// $rules = ['email' => 'required|email'];
  // 	$validator = \Validator::make($contact, $rules);

  // 	if ($validator->fails())
  // 	{
  // 		return $validator;
  // 	}

  	// $invoice = (array) $input;
  	// $invoiceId = isset($invoice['public_id']) && $invoice['public_id'] ? Invoice::getPrivateId($invoice['public_id']) : null;
  	// $rules = ['invoice_number' => 'unique:invoices,invoice_number,' . $invoiceId . ',id,account_id,' . \Auth::user()->account_id];    	

  	// if ($invoice['is_recurring'] && $invoice['start_date'] && $invoice['end_date'])
  	// {
  	// 	$rules['end_date'] = 'after:' . $invoice['start_date'];
  	// }

  	// $validator = \Validator::make($invoice, $rules);

  	// if ($validator->fails())
  	// {
  	// 	return $validator;
  	// }

  	return false;
	}

	public function save($publicId, $data, $entityType)
	{
		if ($publicId) 
		{
			$invoice = Invoice::scope($publicId)->firstOrFail();
		} 
		else 
		{				
			$invoice = Invoice::createNew();			

      if ($entityType == ENTITY_QUOTE)
      {
        $invoice->is_quote = true;
      }
					
		
  		$invoice->client_id = $data['client_id'];
  		$invoice->discount = Utils::parseFloat($data['discount']);

      $discount_item = Utils::parseFloat($data['discount_item']);

  		$invoice->is_recurring = $data['is_recurring'] ? true : false;
      $invoice->invoice_date = Utils::toSqlDate($data['invoice_date']);
        
      if ($invoice->is_recurring)
      {
        $invoice->frequency_id = $data['frequency_id'] ? $data['frequency_id'] : 0;
        $invoice->start_date = Utils::toSqlDate($data['start_date']);
        $invoice->end_date = Utils::toSqlDate($data['end_date']);
        $invoice->due_date = null;
        $invoice->invoice_number = " ";
      }
      else
      {
        if($data['action'] == 'save')
        {
            $date = date("Y-m-d", strtotime(date("Y-m-d")." +1 month"));
            $invoice->due_date = $date;

        }
        else
        {
          $invoice->due_date = Utils::toSqlDate($data['due_date']);

        }

        $invoice->frequency_id = 0;
        $invoice->start_date = null;
        $invoice->end_date = null;

        $invoiceNumber = \Auth::user()->branch->getNextInvoiceNumber();
        $invoice->invoice_number = $invoiceNumber;

      }

  		$invoice->terms = trim($data['terms']);
  		$invoice->public_notes = trim($data['public_notes']);

      $invoiceDesign = \DB::table('invoice_designs')->where('account_id',\Auth::user()->account_id)->orderBy('public_id', 'desc')->first();
      $invoice->invoice_design_id = $invoiceDesign->id;

  		// if (isset($data['tax_name']) && isset($data['tax_rate']) && Utils::parseFloat($data['tax_rate']) > 0)
  		// {
  		// 	$invoice->tax_rate = Utils::parseFloat($data['tax_rate']);
  		// 	$invoice->tax_name = trim($data['tax_name']);
  		// } 
  		// else
  		// {
  		// 	$invoice->tax_rate = 0;
  		// 	$invoice->tax_name = '';
  		// }
  		
  		$total = 0;						
  		
  		foreach ($data['invoice_items'] as $item) 
  		{
  			if (!$item->cost && !$item->product_key && !$item->notes)
  			{
  				continue;
  			}

  			$invoiceItemCost = Utils::parseFloat($item->cost);
  			$invoiceItemQty = Utils::parseFloat($item->qty);
  			$invoiceItemTaxRate = 0;

  			// if (isset($item->tax_rate) && Utils::parseFloat($item->tax_rate) > 0)
  			// {
  			// 	$invoiceItemTaxRate = Utils::parseFloat($item->tax_rate);				
  			// }

  			$lineTotal = $invoiceItemCost * $invoiceItemQty;
        
  			$total += round($lineTotal + ($lineTotal * $invoiceItemTaxRate / 100), 2);
  		}

      $invoice->subtotal = $total;
      
  		if ($invoice->discount > 0)
  		{ 
  			$total *= (100 - $invoice->discount) / 100;
  		}

      if ($discount_item > 0)
      { 
        $total -= $discount_item;
      }

      if ($publicId)    
      {
  		  $invoice->balance = $total - ($invoice->amount - $invoice->balance);
      }
      else
      {
        $invoice->balance = $total; 
      }

      $invoice->amount = $total;



      $account = \Auth::user()->account;

      $branch = \Auth::user()->branch;

      $invoice->account_name=$account->name;
      $invoice->account_nit=$account->nit;

      $invoice->branch_id = \Auth::user()->branch->id;
      $invoice->branch_type_id=$branch->branch_type_id;
      $invoice->branch_name=$branch->name;
      $invoice->address1=$branch->address1;
      $invoice->address2=$branch->address2;
      $invoice->phone=$branch->work_phone;
      $invoice->city=$branch->city;
      $invoice->state=$branch->state;

      $invoice->number_autho=$branch->number_autho;
      $invoice->deadline=$branch->deadline;
      $invoice->key_dosage=$branch->key_dosage;

      $invoice->client_nit = $data['client_nit'];
      $invoice->client_name = $data['client_name'];

      $invoice->economic_activity = $branch->economic_activity;
      $invoice->law = $branch->law;
      
      $invoice->type_third=$branch->type_third;

      $invoice_dateCC = date("Ymd", strtotime($invoice->invoice_date));
      $invoice_date_limitCC = date("d/m/Y", strtotime($branch->deadline));

      require_once(app_path().'/includes/control_code.php');
      $cod_control = codigoControl($invoice->invoice_number, $invoice->client_nit, $invoice_dateCC, $invoice->amount, $branch->number_autho, $branch->key_dosage);
      $invoice->control_code = $cod_control;

      $invoice_date = date("d/m/Y", strtotime($invoice->invoice_date));
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
      $invoice->qr = $invoice->account_nit.'|'.$invoice->invoice_number.'|'.$invoice->number_autho.'|'.$invoice_date.'|'.$subtotal.'|'.$amount.'|'.$invoice->control_code.'|'.$invoice->client_nit.'|'.$icef.'|0|0|'.$descf;

  		$invoice->save();

      $invoice->invoice_items()->forceDelete();
      
      foreach ($data['invoice_items'] as $item) 
      {
        if (!$item->cost && !$item->product_key && !$item->notes)
        {
          continue;
        }

        if ($item->product_key)
        {
          $product = Product::findProductByKey(trim($item->product_key));

          if (!$product)
          {
            $product = Product::createNew();            
            $product->product_key = strtoupper(trim($item->product_key));
          }

          if (\Auth::user()->account->update_products)
          {

            $categories = \DB::table('categories')->where('account_id',\Auth::user()->account_id)->orderBy('public_id', 'desc')->first();

            $product->category_id = $categories->id;
            $product->notes = $item->notes;
            $product->cost = $item->cost;
            //$product->qty = $item->qty;
          }
          
          $product->save();
        }

        $invoiceItem = InvoiceItem::createNew();
        $invoiceItem->product_id = isset($product) ? $product->id : null;
        $invoiceItem->product_key = trim($invoice->is_recurring ? $item->product_key : Utils::processVariables($item->product_key));
        $invoiceItem->notes = trim($invoice->is_recurring ? $item->notes : Utils::processVariables($item->notes));
        $invoiceItem->cost = Utils::parseFloat($item->cost);
        $invoiceItem->qty = Utils::parseFloat($item->qty);
        // $invoiceItem->tax_rate = 0;

        // if (isset($item->tax_rate) && Utils::parseFloat($item->tax_rate) > 0)
        // {
        //   $invoiceItem->tax_rate = Utils::parseFloat($item->tax_rate);
        //   $invoiceItem->tax_name = trim($item->tax_name);
        // }

        $invoice->invoice_items()->save($invoiceItem);
      }
    }
		return $invoice;
	}


	public function bulk($ids, $action, $statusId = false)
	{
		if (!$ids)
		{
			return 0;
		}

		$invoices = Invoice::withTrashed()->scope($ids)->get();

		foreach ($invoices as $invoice) 
		{
      if ($action == 'mark')
      {
        $invoice->invoice_status_id = $statusId;
        $invoice->save();
      } 
      else 
      {
  			if ($action == 'delete') 
  			{
  				$invoice->is_deleted = true;
          $invoice->invoice_status_id = 6;
  				$invoice->save();
          $BookSale = BookSale::scope()->whereInvoiceId($invoice->id)->first();
          $BookSale->status = "A";
          $BookSale->save();
  			}
        else
        {
          $invoice->delete();
        }

  			
      }
		}

		return count($invoices);
	}
}
