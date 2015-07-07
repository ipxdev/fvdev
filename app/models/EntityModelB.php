<?php

class EntityModelB extends Eloquent
{
	protected $softDelete = true;
	public $timestamps = true;
	
	protected $hidden = ['id'];

	public static function createNew($parent = false)
	{		
		$className = get_called_class();
		$entity = new $className();
		
		if ($parent)
		{
			$entity->account_id = $parent->account_id;
		} 
		else if (Auth::check()) 
		{
			$entity->account_id = Auth::user()->account_id;			
		} 
		else 
		{
			Utils::fatalError();
		}

		$lastEntity = $className::withTrashed()->scope(false, $entity->account_id)->orderBy('public_id', 'DESC')->first();

		if ($lastEntity)
		{
			$entity->public_id = $lastEntity->public_id + 1;
		}
		else
		{
			$entity->public_id = 1;
		}
		
		return $entity;
	}

	public static function getPrivateId($publicId)
	{
		$className = get_called_class();
		return $className::scope($publicId)->pluck('id');
	}

	public function getActivityKey()
	{
		return $this->getEntityType() . ':' . $this->public_id . ':' . $this->getName();
	}

	public function scopeScope($query, $publicId = false, $accountId = false)
	{
		if (!$accountId) 
		{
			$accountId = Auth::user()->account_id;
		}
		
		$query->whereAccountId($accountId);

		if ($publicId)
		{
			if (is_array($publicId))
			{
				$query->whereIn('public_id', $publicId);
			}
			else
			{
				$query->wherePublicId($publicId);
			}
		}
		
		return $query;
	}
}