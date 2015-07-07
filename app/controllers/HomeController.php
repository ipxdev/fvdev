<?php

use ninja\mailers\Mailer;

class HomeController extends BaseController {

	protected $layout = 'master';
	protected $mailer;

	public function __construct(Mailer $mailer)
	{
		parent::__construct();

		$this->mailer = $mailer;
	}	

	public function showIndex()
	{
		if (Account::count() == 0)
		{
			return View::make('public.welcome');
		}
		else
		{
			return Redirect::to('/login');
		}
	}
	public function createAccount()
	{
		if (Auth::check())
		{
			return Redirect::to('dashboard');				
		}
		else
		{
			return View::make('public.welcome');
		}
	}

	public function logError()
	{
		return Utils::logError(Input::get('error'), 'JavaScript');
	}

}