<?php

class Product extends EntityModel
{	
	public static function findProductByKey($key)
	{
		return Product::scope()->where('product_key','=',$key)->first();
	}

	public function getEntityType()
	{
		return ENTITY_PRODUCT;
	}

	public function getName()
	{
		return $this->getDisplayName();
	}

	public function getDisplayName()
	{
		if ($this->notes) 
		{
			return $this->notes;
		}
	}

	public function getProductKey()
	{
		if ($this->product_key) 
		{
			return $this->product_key;
		}
	}

	public function getProductCost()
	{
		if ($this->cost) 
		{
			return $this->cost;
		}
	}

	public function category()
	{
		return $this->belongsTo('Category');
	}
}