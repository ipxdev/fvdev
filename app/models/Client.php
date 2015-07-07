<?php

class Client extends EntityModel
{
	public static $fieldVat_number = 'Cliente - Nombre';

	public static $fieldName = 'Facturación - Razón Social';
	public static $fieldNit = 'Facturación - NIT';

	public static $fieldAddress2 = 'Cliente - Dirección';
	public static $fieldAddress1 = 'Cliente - Zona/Barrio';
	public static $fieldPhone = 'Cliente - Teléfono';

	public static $fieldNotes = 'Cliente - Antecedentes';

	public function account()
	{
		return $this->belongsTo('Account');
	}
	
	public function invoices()
	{
		return $this->hasMany('Invoice');
	}

	public function payments()
	{
		return $this->hasMany('Payment');
	}

	public function contacts()
	{
		return $this->hasMany('Contact');
	}

	public function getTotalCredit()
	{
		return DB::table('credits')
				->where('client_id','=',$this->id)
				->whereNull('deleted_at')
				->sum('balance');
	}

	public function getName()
	{
		return $this->getDisplayName();
	}

	public function getCod()
	{
		return 'Código: '.$this->public_id;
	}
	
	public function getNit()
	{
		if(!$this->nit)
		{
			return '';
		}	
		return $this->nit;
	}

	public function getTitular()
	{
		if(!$this->vat_number)
		{
			return '';
		}	
		return $this->vat_number;
	}

	public function getCarnet()
	{
		if(!$this->nit)
		{
			return '';
		}	
		return $this->postal_code;
	}


	public function getFechacontrato()
	{
		if(!$this->nit)
		{
			return '';
		}	
		return 'Fecha de Contrato: '.$this->state;
	}


	public function getDisplayName()
	{
		if ($this->name) 
		{
			return $this->name;
		}

		$this->load('contacts');
		$contact = $this->contacts()->first();
		
		return $contact->getDisplayName();
	}

	public function getEntityType()
	{
		return ENTITY_CLIENT;
	}

	public function getAddress()
	{
		$str = '';

		if ($this->address1) {
			$str .= '<i class="glyphicon glyphicon-home" style="width: 20px"></i>' . $this->address1 . '<br/>';
		}
		if ($this->address2) {
			$str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->address2 . '<br/>';	
		}
		if ($this->country) {
			$str .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $this->country->name;			
		}

		if ($str)
		{
			$str = '<p>' . $str . '</p>';
		}

		return $str;
	}

	public function getPhone()
	{
		$str = '';

		if ($this->work_phone)
		{
			$str .= '<i class="glyphicon glyphicon-phone-alt" style="width: 20px"></i>' . $this->work_phone;
		}

		return $str;
	}
    
 //    public function getVatNumber()
	// {
	// 	$str = '';

	// 	if ($this->work_phone)
	// 	{
	// 		$str .= '<i class="fa fa-vat-number" style="width: 20px"></i>' . $this->vat_number;
	// 	}

	// 	return $str;
	// }
    

	public function getNotes()
	{
		$str = '';

		if ($this->private_notes)
		{
			$str .= '<i>' . $this->private_notes . '</i>';
		}

		return $str;
	}


	public function getCustomFields()
	{
		$str = '';
		$account = $this->account;

		if ($account->custom_client_label1 && $this->custom_value1)
		{
			$str .= "{$account->custom_client_label1}: {$this->custom_value1}<br/>";
		}

		if ($account->custom_client_label2 && $this->custom_value2)
		{
			$str .= "{$account->custom_client_label2}: {$this->custom_value2}<br/>";
		}

		if ($account->custom_client_label3 && $this->custom_value3)
		{
			$str .= "{$account->custom_client_label3}: {$this->custom_value3}<br/>";
		}

		if ($account->custom_client_label4 && $this->custom_value4)
		{
			$str .= "{$account->custom_client_label4}: {$this->custom_value4}<br/>";
		}

		if ($account->custom_client_label5 && $this->custom_value5)
		{
			$str .= "{$account->custom_client_label5}: {$this->custom_value5}<br/>";
		}

		if ($account->custom_client_label6 && $this->custom_value6)
		{
			$str .= "{$account->custom_client_label6}: {$this->custom_value6}<br/>";
		}

		if ($account->custom_client_label7 && $this->custom_value7)
		{
			$str .= "{$account->custom_client_label7}: {$this->custom_value7}<br/>";
		}

		if ($account->custom_client_label8 && $this->custom_value8)
		{
			$str .= "{$account->custom_client_label8}: {$this->custom_value8}<br/>";
		}

		if ($account->custom_client_label9 && $this->custom_value9)
		{
			$str .= "{$account->custom_client_label9}: {$this->custom_value9}<br/>";
		}

		if ($account->custom_client_label10 && $this->custom_value10)
		{
			$str .= "{$account->custom_client_label10}: {$this->custom_value10}<br/>";
		}
		if ($account->custom_client_label11 && $this->custom_value11)
		{
			$str .= "{$account->custom_client_label11}: {$this->custom_value11}<br/>";
		}

		if ($account->custom_client_label12 && $this->custom_value12)
		{
			$str .= "{$account->custom_client_label12}: {$this->custom_value12}<br/>";
		}

		return $str;
	}


	public function getCellular()
	{
		$str = '';

		if ($this->work_phone)
		{
			$str .= '<i class="fa fa-phone" style="width: 20px"></i>' . Utils::formatPhoneNumber($this->website);
		}

		return $str;
	}

	public function getDateCreated()
	{		
		if ($this->created_at == '0000-00-00 00:00:00') 
		{
			return '---';
		} 
		else 
		{
			return $this->created_at->format('m/d/y h:i a');
		}
	}

}

/*
Client::created(function($client)
{
	Activity::createClient($client);
});
*/

Client::updating(function($client)
{
	Activity::updateClient($client);
});

Client::deleting(function($client)
{
	Activity::archiveClient($client);
});