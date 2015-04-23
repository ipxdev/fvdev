<?php

class InvoiceDesign extends EntityModel
{
	public $timestamps = false;
	protected $softDelete = false;  

  	public function account()
	{
		return $this->belongsTo('Account');
	}

	public function branch()
	{
		return $this->belongsTo('Branch');
	}
}
