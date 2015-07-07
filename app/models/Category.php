<?php

class Category extends EntityModel
{	

	public function getEntityType()
	{
		return ENTITY_CATEGORY;
	}

	public function account()
	{
		return $this->belongsTo('Account');
	}
	
	public function users()
	{
		return $this->hasMany('User');
	}

	public function getName()
	{
		return $this->name();
	}
}