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


	public function showSecurePayment()
	{
		return View::make('secure_payment');	
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

	public function invoiceNow()
	{
		if (Auth::check())
		{
			return Redirect::to('dashboard');				
		}
		else
		{
			return View::make('public.header', ['invoiceNow' => true]);
		}
	}

	public function newsFeed($userType, $version)
	{		
		$response = Utils::getNewsFeedResponse($userType);

		return Response::json($response);
	}

	public function hideMessage()
	{		
		if (Auth::check() && Session::has('news_feed_id')) {
			$newsFeedId = Session::get('news_feed_id');
			if ($newsFeedId != NEW_VERSION_AVAILABLE && $newsFeedId > Auth::user()->news_feed_id) {
				$user = Auth::user();
				$user->news_feed_id = $newsFeedId;
				$user->save();
			}

			Session::forget('news_feed_message');
		}

		return 'success';
	}

	public function logError()
	{
		return Utils::logError(Input::get('error'), 'JavaScript');
	}

}