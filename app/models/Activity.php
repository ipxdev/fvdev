<?php


define("ACTIVITY_TYPE_CREATE_CLIENT", 1);
define("ACTIVITY_TYPE_ARCHIVE_CLIENT", 2);
define("ACTIVITY_TYPE_DELETE_CLIENT", 3);
define("ACTIVITY_TYPE_EDIT_CLIENT", 24);

define("ACTIVITY_TYPE_CREATE_INVOICE", 4);
define("ACTIVITY_TYPE_UPDATE_INVOICE", 5);
define("ACTIVITY_TYPE_EMAIL_INVOICE", 6);
define("ACTIVITY_TYPE_VIEW_INVOICE", 7);
define("ACTIVITY_TYPE_ARCHIVE_INVOICE", 8);
define("ACTIVITY_TYPE_DELETE_INVOICE", 9);

define("ACTIVITY_TYPE_CREATE_PAYMENT", 10);
define("ACTIVITY_TYPE_UPDATE_PAYMENT", 11);
define("ACTIVITY_TYPE_ARCHIVE_PAYMENT", 12);
define("ACTIVITY_TYPE_DELETE_PAYMENT", 13);

define("ACTIVITY_TYPE_CREATE_CREDIT", 14);
define("ACTIVITY_TYPE_UPDATE_CREDIT", 15);
define("ACTIVITY_TYPE_ARCHIVE_CREDIT", 16);
define("ACTIVITY_TYPE_DELETE_CREDIT", 17);

define("ACTIVITY_TYPE_CREATE_QUOTE", 18);
define("ACTIVITY_TYPE_UPDATE_QUOTE", 19);
define("ACTIVITY_TYPE_EMAIL_QUOTE", 20);
define("ACTIVITY_TYPE_VIEW_QUOTE", 21);
define("ACTIVITY_TYPE_ARCHIVE_QUOTE", 22);
define("ACTIVITY_TYPE_DELETE_QUOTE", 23);

define("ACTIVITY_TYPE_ADD_CREDIT_INVOICE", 25);

define("ACTIVITY_TYPE_CREATE_PRODUCT", 26);



class Activity extends Eloquent
{
	public $timestamps = true;
	protected $softDelete = false;	

	public function scopeScope($query)
	{
		return $query->whereAccountId(Auth::user()->account_id);
	}

	public function account()
	{
		return $this->belongsTo('Account');
	}

	private static function getBlank($entity = false)
	{
		$activity = new Activity;

		if ($entity) 
		{
			$activity->user_id = $entity instanceof User ? $entity->id : $entity->user_id;
			$activity->account_id = $entity->account_id;
		} 
		else if (Auth::check())
		{
			$activity->user_id = Auth::user()->id;
			$activity->account_id = Auth::user()->account_id;	
		} 
		else 
		{
			Utils::fatalError();
		}

		return $activity;
	}

	public static function addCredit($credit_invoice, $month)
	{		
		$activity = Activity::getBlank();
		$activity->activity_type_id = ACTIVITY_TYPE_ADD_CREDIT_INVOICE;
		$activity->message = "Se añadió " . $credit_invoice. " Facturas a su cuenta por " . $month . "Meses.";
		$activity->save();		
		$activity->save();

	}

	public static function createClient($client)
	{		
		$activity = Activity::getBlank();
		$activity->client_id = $client->id;
		$activity->activity_type_id = ACTIVITY_TYPE_CREATE_CLIENT;
		$activity->message = Utils::encodeActivity(Auth::user(), 'creó', $client);
		$activity->save();		
	}

	public static function editClient($client)
	{		
		$activity = Activity::getBlank();
		$activity->client_id = $client->id;
		$activity->activity_type_id = ACTIVITY_TYPE_EDIT_CLIENT;
		$activity->message = Utils::encodeActivity(Auth::user(), 'editó', $client);
		$activity->save();		

	}

	public static function updateClient($client)
	{		
		if ($client->is_deleted && !$client->getOriginal('is_deleted'))
		{
			$activity = Activity::getBlank();
			$activity->client_id = $client->id;
			$activity->activity_type_id = ACTIVITY_TYPE_DELETE_CLIENT;
			$activity->message = Utils::encodeActivity(Auth::user(), 'borró', $client);
			$activity->save();		
		}
	}

	public static function archiveClient($client)
	{
		if (!$client->is_deleted)
		{
			$activity = Activity::getBlank();
			$activity->client_id = $client->id;
			$activity->activity_type_id = ACTIVITY_TYPE_ARCHIVE_CLIENT;
			$activity->message = Utils::encodeActivity(Auth::user(), 'archivó', $client);
			$activity->balance = $client->balance;
			$activity->save();
		}
	}	


	public static function createProduct($product)
	{		
		$activity = Activity::getBlank();
		$activity->client_id = $product->id;
		$activity->activity_type_id = ACTIVITY_TYPE_CREATE_PRODUCT;
		$activity->message = Utils::encodeActivity(Auth::user(), 'creó', $product);
		$activity->save();		
	}

	public static function createInvoice($invoice)
	{
		if (Auth::check()) 
		{
			$message = Utils::encodeActivity(Auth::user(), 'creó', $invoice);			
		} 
		else 
		{
			$message = Utils::encodeActivity(null, 'creó', $invoice);
		}

		$adjustment = 0;
		$client = $invoice->client;
		if (!$invoice->is_quote && !$invoice->is_recurring)
		{
			$adjustment = $invoice->amount;
			$client->balance = $client->balance + $adjustment;
			$client->save();
		}

		$activity = Activity::getBlank($invoice);
		$activity->invoice_id = $invoice->id;
		$activity->client_id = $invoice->client_id;
		$activity->activity_type_id = $invoice->is_quote ? ACTIVITY_TYPE_CREATE_QUOTE : ACTIVITY_TYPE_CREATE_INVOICE;
		$activity->message = $message;
		$activity->balance = $client->balance;
		$activity->adjustment = $adjustment;
		$activity->save();

	}	

	public static function archiveInvoice($invoice)
	{
		if (!$invoice->is_deleted)
		{
			$activity = Activity::getBlank();
			$activity->invoice_id = $invoice->id;
			$activity->client_id = $invoice->client_id;
			$activity->activity_type_id = $invoice->is_quote ? ACTIVITY_TYPE_ARCHIVE_QUOTE : ACTIVITY_TYPE_ARCHIVE_INVOICE;
			$activity->message = Utils::encodeActivity(Auth::user(), 'archivó', $invoice);
			$activity->balance = $invoice->client->balance;

			$activity->save();
		}
	}

	public static function emailInvoice($invitation)
	{
		$adjustment = 0;
		$client = $invitation->invoice->client;

		$activity = Activity::getBlank($invitation);
		$activity->client_id = $invitation->invoice->client_id;
		$activity->invoice_id = $invitation->invoice_id;
		$activity->contact_id = $invitation->contact_id;
		$activity->activity_type_id = $invitation->invoice ? ACTIVITY_TYPE_EMAIL_QUOTE : ACTIVITY_TYPE_EMAIL_INVOICE;
		$activity->message = Utils::encodeActivity(Auth::check() ? Auth::user() : null, 'Envió', $invitation->invoice, $invitation->contact);
		$activity->balance = $client->balance;
		$activity->save();
	}

	public static function updateInvoice($invoice)
	{
		$client = $invoice->client;

		if ($invoice->is_deleted && !$invoice->getOriginal('is_deleted'))
		{
			if (!$invoice->is_quote && !$invoice->is_recurring)
			{
				$client->balance = $client->balance - $invoice->balance;
				$client->paid_to_date = $client->paid_to_date - ($invoice->amount - $invoice->balance);
				$client->save();
			}

			$activity = Activity::getBlank();
			$activity->client_id = $invoice->client_id;
			$activity->invoice_id = $invoice->id;
			$activity->activity_type_id = $invoice->is_quote ? ACTIVITY_TYPE_DELETE_QUOTE : ACTIVITY_TYPE_DELETE_INVOICE;
			$activity->message = Utils::encodeActivity(Auth::user(), 'anuló', $invoice);
			$activity->balance = $invoice->client->balance;
			$activity->adjustment = $invoice->is_quote ? 0 : $invoice->balance * -1;
			$activity->save();		
		}
		else
		{
			$diff = floatval($invoice->amount) - floatval($invoice->getOriginal('amount'));
			
			if ($diff == 0)
			{
				return;
			}

			$backupInvoice = Invoice::with('invoice_items', 'client.account', 'client.contacts')->find($invoice->id);

			if (!$invoice->is_quote && !$invoice->is_recurring)
			{
				$client->balance = $client->balance + $diff;
				$client->save();
			}

			$activity = Activity::getBlank($invoice);
			$activity->client_id = $invoice->client_id;
			$activity->invoice_id = $invoice->id;
			$activity->activity_type_id = $invoice->is_quote ? ACTIVITY_TYPE_UPDATE_QUOTE : ACTIVITY_TYPE_UPDATE_INVOICE;
			$activity->message = Utils::encodeActivity(Auth::user(), 'modificó', $invoice);
			$activity->balance = $client->balance;
			$activity->adjustment = $invoice->is_quote || $invoice->is_recurring ? 0 : $diff;
			$activity->json_backup = $backupInvoice->hidePrivateFields()->toJSON();
			$activity->save();
		}
	}

	public static function viewInvoice($invitation)
	{
		if (Session::get($invitation->invitation_key))
		{
			return;
		}

		Session::put($invitation->invitation_key, true);
		$invoice = $invitation->invoice;
		
		if (!$invoice->isViewed())
		{
			$invoice->invoice_status_id = INVOICE_STATUS_VIEWED;
			$invoice->save();
		}
		
		$now = Carbon::now()->toDateTimeString();

		$invitation->viewed_date = $now;
		$invitation->save();

		$client = $invoice->client;
		$client->last_login = $now;
		$client->save();

		$activity = Activity::getBlank($invitation);
		$activity->client_id = $invitation->invoice->client_id;
		$activity->invitation_id = $invitation->id;
		$activity->contact_id = $invitation->contact_id;
		$activity->invoice_id = $invitation->invoice_id;
		$activity->activity_type_id = $invitation->invoice->is_quote ? ACTIVITY_TYPE_VIEW_QUOTE : ACTIVITY_TYPE_VIEW_INVOICE;
		$activity->message = Utils::encodeActivity($invitation->contact, 'vió', $invitation->invoice);
		$activity->balance = $invitation->invoice->client->balance;
		$activity->save();
	}



	public static function createPayment($payment)
	{
		$client = $payment->client;
		$client->balance = $client->balance - $payment->amount;
		$client->paid_to_date = $client->paid_to_date + $payment->amount;
		$client->save();

		if ($payment->contact_id)
		{
			$activity = Activity::getBlank($client);
			$activity->contact_id = $payment->contact_id;
			$activity->message = Utils::encodeActivity($payment->invitation->contact, 'ingresó ' . $payment->getName() . ' a ', $payment->invoice);
		}
		else
		{
			$activity = Activity::getBlank($client);
			$message = $payment->payment_type_id == PAYMENT_TYPE_CREDIT ? 'aplicó crédito a ' : 'ingresó ' . $payment->getName() . ' a ';
			$activity->message = Utils::encodeActivity(Auth::user(), $message, $payment->invoice);
		}

		$activity->payment_id = $payment->id;

		if ($payment->invoice_id) 
		{
			$activity->invoice_id = $payment->invoice_id;

			$invoice = $payment->invoice;
			$invoice->balance = $invoice->balance - $payment->amount;
			$invoice->invoice_status_id = ($invoice->balance > 0) ? INVOICE_STATUS_PARTIAL : INVOICE_STATUS_PAID;
			$invoice->save();
		}

		$activity->payment_id = $payment->id;
		$activity->client_id = $payment->client_id;
		$activity->activity_type_id = ACTIVITY_TYPE_CREATE_PAYMENT;
		$activity->balance = $client->balance;
		$activity->adjustment = $payment->amount * -1;
		$activity->save();

	}	

	public static function updatePayment($payment)
	{
		if ($payment->is_deleted && !$payment->getOriginal('is_deleted'))
		{
			$client = $payment->client;
			$client->balance = $client->balance + $payment->amount;
			$client->paid_to_date = $client->paid_to_date - $payment->amount;
			$client->save();

			$invoice = $payment->invoice;
			$invoice->balance = $invoice->balance + $payment->amount;
			$invoice->save();

			$activity = Activity::getBlank();
			$activity->payment_id = $payment->id;
			$activity->client_id = $invoice->client_id;
			$activity->invoice_id = $invoice->id;
			$activity->activity_type_id = ACTIVITY_TYPE_DELETE_PAYMENT;
			$activity->message = Utils::encodeActivity(Auth::user(), 'borró ' . $payment->getName());
			$activity->balance = $client->balance;
			$activity->adjustment = $payment->amount;
			$activity->save();		
		}
		else
		{
			/*
			$diff = floatval($invoice->amount) - floatval($invoice->getOriginal('amount'));
			
			if ($diff == 0)
			{
				return;
			}

			$client = $invoice->client;
			$client->balance = $client->balance + $diff;
			$client->save();

			$activity = Activity::getBlank($invoice);
			$activity->client_id = $invoice->client_id;
			$activity->invoice_id = $invoice->id;
			$activity->activity_type_id = ACTIVITY_TYPE_UPDATE_INVOICE;
			$activity->message = Utils::encodeActivity(Auth::user(), 'updated', $invoice);
			$activity->balance = $client->balance;
			$activity->adjustment = $diff;
			$activity->json_backup = $backupInvoice->hidePrivateFields()->toJSON();
			$activity->save();
			*/
		}
	}

	public static function archivePayment($payment)
	{
		if ($payment->is_deleted)
		{
			return;
		}

		$client = $payment->client;
		$invoice = $payment->invoice;

		$activity = Activity::getBlank();
		$activity->payment_id = $payment->id;
		$activity->invoice_id = $invoice->id;
		$activity->client_id = $client->id;
		$activity->activity_type_id = ACTIVITY_TYPE_ARCHIVE_PAYMENT;
		$activity->message = Utils::encodeActivity(Auth::user(), 'archivó ' . $payment->getName());
		$activity->balance = $client->balance;
		$activity->adjustment = 0;
		$activity->save();
	}	


	public static function createCredit($credit)
	{
		$activity = Activity::getBlank();
		$activity->message = Utils::encodeActivity(Auth::user(), 'ingreso ' . Utils::formatMoney($credit->amount, $credit->client->currency_id) . ' de crédito');
		$activity->credit_id = $credit->id;
		$activity->client_id = $credit->client_id;
		$activity->activity_type_id = ACTIVITY_TYPE_CREATE_CREDIT;
		$activity->balance = $credit->client->balance;
		$activity->save();
	}	

	public static function updateCredit($credit)
	{
		if ($credit->is_deleted && !$credit->getOriginal('is_deleted'))
		{
			$activity = Activity::getBlank();
			$activity->credit_id = $credit->id;
			$activity->client_id = $credit->client_id;
			$activity->activity_type_id = ACTIVITY_TYPE_DELETE_CREDIT;
			$activity->message = Utils::encodeActivity(Auth::user(), 'borró ' . Utils::formatMoney($credit->balance, $credit->client->currency_id) . ' de crédito');
			$activity->balance = $credit->client->balance;
			$activity->save();		
		}
		else
		{
			/*
			$diff = floatval($invoice->amount) - floatval($invoice->getOriginal('amount'));
			
			if ($diff == 0)
			{
				return;
			}

			$client = $invoice->client;
			$client->balance = $client->balance + $diff;
			$client->save();

			$activity = Activity::getBlank($invoice);
			$activity->client_id = $invoice->client_id;
			$activity->invoice_id = $invoice->id;
			$activity->activity_type_id = ACTIVITY_TYPE_UPDATE_INVOICE;
			$activity->message = Utils::encodeActivity(Auth::user(), 'updated', $invoice);
			$activity->balance = $client->balance;
			$activity->adjustment = $diff;
			$activity->json_backup = $backupInvoice->hidePrivateFields()->toJSON();
			$activity->save();
			*/
		}
	}

	public static function archiveCredit($credit)
	{
		if ($credit->is_deleted)
		{
			return;
		}
	
		$activity = Activity::getBlank();
		$activity->client_id = $credit->client_id;
		$activity->credit_id = $credit->id;
		$activity->activity_type_id = ACTIVITY_TYPE_ARCHIVE_CREDIT;
		$activity->message = Utils::encodeActivity(Auth::user(), 'archivó ' . Utils::formatMoney($credit->balance, $credit->client->currency_id) . ' de crédito');
		$activity->balance = $credit->client->balance;
		$activity->save();
	}

}