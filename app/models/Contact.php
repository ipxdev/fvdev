<?php

class Contact extends EntityModel
{
	public static $fieldFirstName = 'Contacto - Nombre(s)';
	public static $fieldLastName = 'Contacto - Apellidos';
	public static $fieldEmail = 'Contacto - Correo electrónico';
	public static $fieldPhone = 'Contacto - Celular';

	public function client()
	{
		return $this->belongsTo('Client');
	}

	public function getPersonType()
	{
		return PERSON_CONTACT;
	}

	/*
	public function getLastLogin()
	{
		if ($this->last_login == '0000-00-00 00:00:00') 
		{
			return '---';
		} 
		else 
		{
			return $this->last_login->format('m/d/y h:i a');
		}
	}
	*/
	
	public function getDisplayName()
	{
		if ($this->getFullName())
		{
			return $this->getFullName();
		}
		else
		{
			return $this->email;
		}

	}

	public function getFullName()
	{
		if ($this->first_name || $this->last_name)
		{
			return $this->first_name . ' ' . $this->last_name;
		}
		else
		{
			return '';
		}
	}

	public function getDetails()
	{
		$str = '';
		
		if ($this->first_name || $this->last_name)
		{
			$str .= '<b>' . $this->first_name . ' ' . $this->last_name . '</b><br/>';
		}

		if ($this->email)
		{
			$str .= '<i class="fa fa-envelope" style="width: 20px"></i>' . HTML::mailto($this->email, $this->email) . '<br/>';
		}

		if ($this->phone)
		{
			$str .= '<i class="glyphicon glyphicon-earphone" style="width: 20px"></i>' . $this->phone. '<br/>';
		}

		if ($str)
		{
			$str = '<p>' . $str . '</p>';
		}

		return $str;
	}
}