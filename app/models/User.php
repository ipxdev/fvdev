<?php

use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Zizaco\Confide\ConfideUser;

class User extends ConfideUser implements UserInterface, RemindableInterface
{
	protected $softDelete = true;

    public static $rules = array(
    	/*
    	'username' => 'required|unique:users',
        'password' => 'required|between:6,32|confirmed',
        'password_confirmation' => 'between:6,32',        
        */
    );

    protected $updateRules = array(
    	/*
    	'email' => 'required|unique:users',
		'username' => 'required|unique:users',
		*/
    );

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	public function account()
	{
		return $this->belongsTo('Account');
	}

	public function branch()
	{
		return $this->belongsTo('Branch');
	}

	public function getId()
	{
		return $this->id;
	}

	/**
	 * Get the unique identifier for the user.
	 *
	 * @return mixed
	 */
	public function getAuthIdentifier()
	{		
		return $this->getKey();
	}

	/**
	 * Get the password for the user.
	 *
	 * @return string
	 */
	public function getAuthPassword()
	{
		return $this->password;
	}

	/**
	 * Get the e-mail address where password reminders are sent.
	 *
	 * @return string
	 */
	public function getReminderEmail()
	{
		return $this->email;
	}

	public function isPro()
	{
		return $this->account->isPro();
	}

	public function getDisplayName()
	{
		if ($this->getFullName())
		{
			return $this->getFullName();
		}
		else if ($this->email)
		{
			return $this->email;
		}
		else
		{
			return 'Nombre de Usuario';
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


	public function getMaxNumClients()
	{
		return MAX_NUM_CLIENTS;
	}

	public function isAdmin()
	{

        if($this->is_admin == 1)
        {
        	return true;
        }
        else
        {
        	return false;
        }
	}
}