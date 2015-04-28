<?php

class BookSale extends Eloquent
{
	public $timestamps = true;
	protected $softDelete = false;	

	public function scopeScope($query)
	{
		return $query->whereAccountId(Auth::user()->account_id);
	}
	private static function getBlank($entity = false)
	{
		$BookSale = new BookSale;

		if ($entity) 
		{
			$BookSale->user_id = $entity->user_id;
			$BookSale->account_id = $entity->account_id;
			$BookSale->branch_id = $entity->branch_id;
		} 
		else if (Auth::check())
		{
			$BookSale->user_id = Auth::user()->id;
			$BookSale->account_id = Auth::user()->account_id;	
			$BookSale->branch_id = Auth::user()->branch_id;	
		} 
		else 
		{
			Utils::fatalError();
		}

		return $BookSale;
	}

	public static function createBook($invoice)
	{

		if (!$invoice->is_recurring)
		{		
		$BookSale = BookSale::getBlank($invoice);

		$branch = $invoice->branch;

		$BookSale->invoice_id = $invoice->id;

		$BookSale->invoice_date = date("d/m/Y", strtotime($invoice->invoice_date));
		$BookSale->invoice_number = $invoice->invoice_number;

		$BookSale->number_autho = $branch->number_autho;
		$BookSale->status = "V";

		$BookSale->client_nit = $invoice->client_nit;
		$BookSale->client_name = $invoice->client_name;

		$BookSale->amount = $invoice->subtotal;
		$BookSale->ice_amount = 0;
		$BookSale->export_amount = 0;
		$BookSale->grav_amount = 0;

		$BookSale->subtotal = $invoice->subtotal-$BookSale->ice_amount-$BookSale->export_amount-$BookSale->grav_amount;
		
		$BookSale->disc_bonus_amount = $invoice->subtotal-$invoice->amount;;

		$BookSale->base_fiscal_debit_amount = $BookSale->subtotal-$BookSale->disc_bonus_amount;
		
		$aux = $BookSale->base_fiscal_debit_amount*13/100;
		
		$BookSale->fiscal_debit_amount = $aux;

		$BookSale->control_code = $invoice->control_code;

		$BookSale->save();
		}
	}	

	public static function deleteBook($invoice)
	{

		if ($invoice->is_deleted)
		{
			$BookSale = BookSale::scope()->whereInvoiceId($invoice->id)->first();
			$BookSale->status = "A";
			$BookSale->save();
		}

	}
}