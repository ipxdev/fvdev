<?php namespace ninja\repositories;

use Client;
use Contact;
use Account;
use Request;
use Session;
use Language;
use User;
use Auth;
use Invitation;
use Invoice;
use InvoiceItem;
use InvoiceDesign;
use Category;

class AccountRepository
{

	public function getSearchData()
	{
    	$clients = \DB::table('clients')
			->where('clients.deleted_at', '=', null)
			->where('clients.account_id', '=', \Auth::user()->account_id)			
			->whereRaw("clients.vat_number <> ''")
			->select(\DB::raw("'Clients' as type, clients.public_id, clients.vat_number, '' as token"));

		$contacts = \DB::table('clients')
			->join('contacts', 'contacts.client_id', '=', 'clients.id')
			->where('clients.deleted_at', '=', null)
			->where('clients.account_id', '=', \Auth::user()->account_id)
			->whereRaw("CONCAT(contacts.first_name, contacts.last_name, contacts.email) <> ''")
			->select(\DB::raw("'Contacts' as type, clients.public_id, CONCAT(contacts.first_name, ' ', contacts.last_name, ' ', contacts.email) as name, '' as token"));

		$invoices = \DB::table('clients')
			->join('invoices', 'invoices.client_id', '=', 'clients.id')
			->where('clients.account_id', '=', \Auth::user()->account_id)
			->where('clients.deleted_at', '=', null)
			->where('invoices.deleted_at', '=', null)
			->select(\DB::raw("'Invoices' as type, invoices.public_id, CONCAT(invoices.invoice_number, ': ', clients.vat_number) as vat_number, invoices.invoice_number as token"));			

		$data = [];

		foreach ($clients->union($contacts)->union($invoices)->get() as $row)
		{
			$type = $row->type;

			if (!isset($data[$type]))
			{
				$data[$type] = [];	
			}			

			$tokens = explode(' ', $row->vat_number);
			$tokens[] = $type;

			if ($type == 'Invoices')
			{
				$tokens[] = intVal($row->token) . '';
			}

			$data[$type][] = [
				'value' => $row->vat_number,
				'public_id' => $row->public_id,
				'tokens' => $tokens
			];
		}
		
    	return $data;
	}

	public function enablePlan()
	{		
           
		$ninjaAccount = $this->getNinjaAccount();		
		$ninjaClient = $this->getNinjaClient($ninjaAccount);

		$result = 1;
		return $result;
	}


	public function enableProPlan()
	{		
		if (Auth::user()->isPro())
		{
			return false;
		}

		$account = Auth::user()->account;
		$credit = $account->credit_counter;
		$account->credit_counter = $credit +100;

        $date = date("Y-m-d", strtotime(date_create()->format('Y-m-d')." +3 month"));

        $account->pro_plan_paid = $date;
        $account->save();
            
		$result = 1;
		return $result;
	}


	public function getNinjaAccount()
	{
		$account = Account::whereAccountKey(IPX_ACCOUNT_KEY)->first();

		if ($account)
		{
			return $account;	
		}
		else
		{
			$account = new Account();
			$account->nit = '3457229010';
			$account->name = 'IPX SERVER';
			$account->work_phone = '2315725';
			$account->address1 = 'Central';
			$account->address2 = 'Av. 16 de Julio 1456 Edif. Caracas Piso: 2';
			$account->account_key = IPX_ACCOUNT_KEY;
			$account->currency_id = 1;
			$account->language_id = 1;
			$account->confirmed = true;
			$account->op1 = true;
			$account->op2 = true;
			$account->op3 = true;

			$account->save();	

			$user = new User();
			$user->registered = true;
			$user->confirmed = true;
			$user->email = 'fllanos@ipxserver.com';
			$user->password = '4rc4ng3l3$';
			$user->password_confirmation = '4rc4ng3l3$';			
			$user->username = '4dm1n@3457229010';
			$user->first_name = 'FRANKLIN';
			$user->last_name = 'LLANOS SILVA';
			$account->users()->save($user);	

			$category = new Category;
			$category->user_id =$user->getId();
			$category->name = "General";
			$category->public_id = 1;
			$account->categories()->save($category);

			$InvoiceDesign = new InvoiceDesign;
			$InvoiceDesign->user_id =$user->getId();
			$InvoiceDesign->logo = "";
			$InvoiceDesign->javascript = "displaytittle(doc, invoice, layout);

displayHeader(doc, invoice, layout);

doc.setFontSize(11);
doc.setFontType('normal');

var activi = invoice.economic_activity;
var activityX = 565 - (doc.getStringUnitWidth(activi) * doc.internal.getFontSize());
doc.text(activityX, layout.headerTop+45, activi);

var aleguisf_date = getInvoiceDate(invoice);

layout.headerTop = 50;
layout.tableTop = 190;
doc.setLineWidth(0.8);        
doc.setFillColor(255, 255, 255);
doc.roundedRect(layout.marginLeft - layout.tablePadding, layout.headerTop+95, 572, 35, 2, 2, 'FD');

var marginLeft1=30;
var marginLeft2=80;
var marginLeft3=180;
var marginLeft4=220;

datos1y = 160;
datos1xy = 15;
doc.setFontSize(11);
doc.setFontType('bold');
doc.text(marginLeft1, datos1y, 'Fecha : ');
doc.setFontType('normal');

doc.text(marginLeft2-5, datos1y, aleguisf_date);

doc.setFontType('bold');
doc.text(marginLeft1, datos1y+datos1xy, 'Señor(es) :');
doc.setFontType('normal');
doc.text(marginLeft2+15, datos1y+datos1xy, invoice.client_name);

doc.setFontType('bold');
doc.text(marginLeft3+240, datos1y+datos1xy, 'NIT/CI :');
doc.setFontType('normal');
doc.text(marginLeft4+245, datos1y+datos1xy, invoice.client_nit);

doc.setDrawColor(241,241,241);
doc.setFillColor(241,241,241);
doc.rect(layout.marginLeft - layout.tablePadding, layout.headerTop+140, 572, 20, 'FD');

doc.setFontSize(10);
doc.setFontType('bold');

if(invoice.branch_type_id==1)
{

    displayInvoiceHeader2(doc, invoice, layout);
	var y = displayInvoiceItems2(doc, invoice, layout);
	displayQR(doc, layout, invoice, y);
	y += displaySubtotals2(doc, layout, invoice, y+15, layout.unitCostRight+35);
}
if(invoice.branch_type_id==2)
{
    displayInvoiceHeader2(doc, invoice, layout);
	var y = displayInvoiceItems2(doc, invoice, layout);
	displayQR(doc, layout, invoice, y);
	y += displaySubtotals2(doc, layout, invoice, y+15, layout.unitCostRight+35);
}

y -=10;		
displayNotesAndTerms(doc, layout, invoice, y);";
			$account->invoice_designs()->save($InvoiceDesign);		

		}

		return $account;
	}

	private function getNinjaClient($ninjaAccount)
	{
		$client = Client::whereAccountId($ninjaAccount->id)->wherePublicId(Auth::user()->account_id)->first();

		if (!$client)
		{
			$client = new Client;		
			$client->public_id = Auth::user()->account_id;
			$client->user_id = $ninjaAccount->users()->first()->id;			
			foreach (['nit','name', 'address1', 'address2', 'city', 'state', 'work_phone'] as $field) 
			{
				$client->$field = Auth::user()->account->$field;
				if($field=='name')
				{
					$client->name = Auth::user()->account->$field;
					$client->business_name = Auth::user()->account->$field;

				}
			}		

			$ninjaAccount->clients()->save($client);

			$contact = new Contact;
			$contact->user_id = $ninjaAccount->users()->first()->id;
			$contact->account_id = $ninjaAccount->id;
			$contact->public_id = Auth::user()->account_id;
			$contact->is_primary = true;
			foreach (['first_name', 'last_name', 'email', 'phone'] as $field) 
			{
				$contact->$field = Auth::user()->$field;	
			}		
			$client->contacts()->save($contact);			
		}

		return $client;
	}

}