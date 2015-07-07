<?php

class Branch extends EntityModelB
{

	protected $softDelete = true;	

	public function account()
	{
		return $this->belongsTo('Account');
	}

	public function invoices()
	{
		return $this->hasMany('Invoice');
	}

	public function country()
	{
		return $this->belongsTo('Country');
	}

	public function industry()
	{
		return $this->belongsTo('Industry');
	}
	public function getId()
	{
		return $this->id;
	}
	public function name()
	{
		return $this->name;
	}

	public function isThird()
	{
		return $this->third;
	}
	
	public function getNextInvoiceNumber($isQuote = false)
	{
		$counter = $isQuote ? $this->quote_number_counter : $this->invoice_number_counter;
		$prefix = $isQuote ? $this->quote_number_prefix : $this->invoice_number_prefix;

		return $prefix . $counter;
	}

	public function incrementCounter($isQuote = false) 
	{
		if ($isQuote) {
			$this->quote_number_counter += 1;
		} else {
			$this->invoice_number_counter += 1;
		}

		$this->save();
	}

	public function isValid1()
	{


		if (is_null($this->deadline))
		{
			return false;
		}
		else
		{
			if ($this->deadline == '0000-00-00')
			{
				return true;
			}
			else
			{
				$datelimit1 = $this->deadline;	
				$today = new DateTime('now');

				$today = $today->format('Y-m-d');
				$datelimit = DateTime::createFromFormat('Y-m-d', $datelimit1);	
				$datelimit = $datelimit->format('Y-m-d');

				$valoresPrimera = explode ("-", $datelimit); 
				$valoresSegunda = explode ("-", $today); 

				$diaPrimera    = $valoresPrimera[2];  
				$mesPrimera  = $valoresPrimera[1];  
				$anyoPrimera   = $valoresPrimera[0]; 

				$diaSegunda   = $valoresSegunda[2];  
				$mesSegunda = $valoresSegunda[1];  
				$anyoSegunda  = $valoresSegunda[0];

				$diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
				$diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);     

				return  $diasPrimeraJuliano - $diasSegundaJuliano < 0;
			}
		}
	}

	public function isValid()
	{	

		$datelimit1 = $this->deadline;	

		if (!$datelimit1)
		{
			return false;
		}
		else
		{

		$today = new DateTime('now');

		$today = $today->format('Y-m-d');
		$datelimit = DateTime::createFromFormat('Y-m-d', $datelimit1);	
		$datelimit = $datelimit->format('Y-m-d');

		$valoresPrimera = explode ("-", $datelimit); 
		$valoresSegunda = explode ("-", $today); 

		$diaPrimera    = $valoresPrimera[2];  
		$mesPrimera  = $valoresPrimera[1];  
		$anyoPrimera   = $valoresPrimera[0]; 

		$diaSegunda   = $valoresSegunda[2];  
		$mesSegunda = $valoresSegunda[1];  
		$anyoSegunda  = $valoresSegunda[0];

		$diasPrimeraJuliano = gregoriantojd($mesPrimera, $diaPrimera, $anyoPrimera);  
		$diasSegundaJuliano = gregoriantojd($mesSegunda, $diaSegunda, $anyoSegunda);     

		return  $diasPrimeraJuliano - $diasSegundaJuliano < 0;

		}

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
	


}
