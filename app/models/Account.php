<?php

class Account extends Eloquent
{
	protected $softDelete = true;

	public function branches()
	{
		return $this->hasMany('Branch');
	}
	
	public function users()
	{
		return $this->hasMany('User');
	}

	public function clients()
	{
		return $this->hasMany('Client');
	}

	public function invoices()
	{
		return $this->hasMany('Invoice');
	}

	public function tax_rates()
	{
		return $this->hasMany('TaxRate');
	}

	public function invoice_designs()
	{
		return $this->hasMany('InvoiceDesign');
	}

	public function timezone()
	{
		return $this->belongsTo('Timezone');
	}

	public function language()
	{
		return $this->belongsTo('Language');
	}

	public function date_format()
	{
		return $this->belongsTo('DateFormat');	
	}

	public function datetime_format()
	{
		return $this->belongsTo('DatetimeFormat');	
	}

	public function categories()
	{
		return $this->hasMany('Category');
	}

	public function isGatewayConfigured($gatewayId = 0)
	{
		$this->load('account_gateways');

		if ($gatewayId)
		{
			return $this->getGatewayConfig($gatewayId) != false;
		}
		else
		{
			return count($this->account_gateways) > 0;
		}
	}

	public function getName()
	{
		if ($this->name) 
		{
			return $this->name;
		}
	}

	public function getNit()
	{
		if ($this->nit) 
		{
			return $this->nit;
		}
	}

	public function getUniper()
	{
		if ($this->uniper) 
		{
			return $this->uniper;
		}
	}

	public function getOp1()
	{
		if ($this->op1) 
		{
			return true;
		}
	}
	
	public function getOp2()
	{
		if ($this->op2) 
		{
			return true;
		}
	}

	public function getOp3()
	{
		if ($this->op3) 
		{
			return true;
		}
	}

	public function getDisplayName()
	{
		if ($this->name) 
		{
			return $this->name;
		}

		$this->load('users');
		$user = $this->users()->first();
		
		return $user->getDisplayName();
	}

	public function getCreditCounter()
	{
		return $this->credit_counter;
	}

	public function getTimezone()
	{
		if ($this->timezone)
		{
			return $this->timezone->name;
		}
		else
		{
			return 'America/La_Paz';
		}
	}

	public function getLocale() 
	{
		$language = Language::remember(DEFAULT_QUERY_CACHE)->where('id', '=', $this->account->language_id)->first();		
		return $language->locale;		
	}

	public function loadLocalizationSettings()
	{
		$this->load('timezone', 'date_format', 'datetime_format', 'language');

		Session::put(SESSION_TIMEZONE, $this->timezone ? $this->timezone->name : DEFAULT_TIMEZONE);
		Session::put(SESSION_DATE_FORMAT, $this->date_format ? $this->date_format->format : DEFAULT_DATE_FORMAT);
		Session::put(SESSION_DATE_PICKER_FORMAT, $this->date_format ? $this->date_format->picker_format : DEFAULT_DATE_PICKER_FORMAT);
		Session::put(SESSION_DATETIME_FORMAT, $this->datetime_format ? $this->datetime_format->format : DEFAULT_DATETIME_FORMAT);			
		Session::put(SESSION_CURRENCY, $this->currency_id ? $this->currency_id : DEFAULT_CURRENCY);		
		Session::put(SESSION_LOCALE, $this->language_id ? $this->language->locale : DEFAULT_LOCALE);
	}

	public function getInvoiceLabels()
	{
		$data = [];
		$fields = [ 
			'invoice',  		
  		'invoice_date',
  		'due_date',
  		'invoice_number',
		  'po_number',
		  'discount',
  		'taxes',
  		'tax',
  		'item',
  		'description',
  		'unit_cost',
  		'quantity',
  		'line_total',
  		'subtotal',
  		'paid_to_date',
  		'balance_due',
  		'terms',
  		'your_invoice',
  		'quote',
  		'your_quote',
  		'quote_date',
  		'quote_number',
  		'total',
  		'invoice_issued_to',
		];

		foreach ($fields as $field)
		{
			$data[$field] = trans("texts.$field");
		}

		return $data;
	}

	public function isRegistered()
	{

		if ($this->account_key == IPX_ACCOUNT_KEY)
		{
			return true;
		}

		$datePaid = $this->pro_plan_paid;
		if (!$datePaid == '0000-00-00')
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function isPro()
	{

		if ($this->account_key == IPX_ACCOUNT_KEY)
		{
			return true;
		}
		
		$datePaid = $this->pro_plan_paid;
		if (!$datePaid || $datePaid == '0000-00-00')
		{
			return false;
		}

		$today = new DateTime('now');
		$datePaid = DateTime::createFromFormat('Y-m-d', $datePaid);		
		if($datePaid >= $today)
		{
			return true;
		}
		else
		{
			return false;
		}

		if ($this->credit_counter > 0)
		{
			return true;
		}
		else
		{	
			return false;
		}
	}

	public function hideFieldsForViz()
	{
		foreach ($this->clients as $client)
		{
			$client->setVisible([
				'public_id',
				'name', 
				'balance',
				'paid_to_date',
				'invoices',
				'contacts',
			]);
			
			foreach ($client->invoices as $invoice) 
			{
				$invoice->setVisible([
					'public_id',
					'invoice_number',
					'amount',
					'balance',
					'invoice_status_id',
					'invoice_items',
					'created_at',
				]);

				foreach ($invoice->invoice_items as $invoiceItem) 
				{
					$invoiceItem->setVisible([
						'product_key',
						'cost', 
						'qty',
					]);
				}			
			}

			foreach ($client->contacts as $contact) 
			{
				$contact->setVisible([
					'public_id',
					'first_name',
					'last_name',
					'email']);
			}						
		}

		return $this;
	}

}