<?php

use ninja\repositories\PaymentRepository;
use ninja\repositories\InvoiceRepository;
use ninja\repositories\AccountRepository;
use ninja\mailers\ContactMailer;

class PaymentController extends \BaseController 
{
    protected $creditRepo;

    public function __construct(PaymentRepository $paymentRepo, InvoiceRepository $invoiceRepo, AccountRepository $accountRepo, ContactMailer $contactMailer)
    {
        parent::__construct();

        $this->paymentRepo = $paymentRepo;
        $this->invoiceRepo = $invoiceRepo;
        $this->accountRepo = $accountRepo;
        $this->contactMailer = $contactMailer;
    }   

    public function index()
    {
        return View::make('list', array(
            'entityType'=>ENTITY_PAYMENT, 
            'title' => trans('texts.payments'),
            'columns'=>Utils::trans(['checkbox', 'invoice', 'client', 'transaction_reference', 'method', 'payment_amount', 'payment_date', 'action'])
        ));
    }

    public function getDatatable($clientPublicId = null)
    {
        $payments = $this->paymentRepo->find($clientPublicId, Input::get('sSearch'));
        $table = Datatable::query($payments);        

        if (!$clientPublicId) {
            $table->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; });
        }

        $table->addColumn('invoice_number', function($model) { return $model->invoice_public_id ? link_to('invoices/' . $model->invoice_public_id . '/edit', $model->invoice_number) : ''; });

        if (!$clientPublicId) {
            $table->addColumn('client_name', function($model) { return link_to('clients/' . $model->client_public_id, Utils::getClientDisplayName($model)); });
        }        

        $table->addColumn('transaction_reference', function($model) { return $model->transaction_reference ? $model->transaction_reference : '<i>Pagado</i>'; })
              ->addColumn('payment_type', function($model) { return $model->payment_type ? $model->payment_type : ($model->account_gateway_id ? '<i>Pago en línea</i>' : ''); });

        return $table->addColumn('amount', function($model) { return Utils::formatMoney($model->amount, 1); })
            ->addColumn('payment_date', function($model) { return Utils::dateToString($model->payment_date); })
            ->addColumn('dropdown', function($model) 
            { 
                return '<div class="btn-group tr-action" style="visibility:hidden;">
                            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                            '.trans('texts.select').' <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                            <li><a href="javascript:archiveEntity(' . $model->public_id. ')">'.trans('texts.archive_payment').'</a></li>
                            <li><a href="javascript:deleteEntity(' . $model->public_id. ')">'.trans('texts.delete_payment').'</a></li>                          
                          </ul>
                        </div>';
            })         
            ->make();
    }


    public function create($clientPublicId = 0, $invoicePublicId = 0)
    {       
        $data = array(
            'clientPublicId' => Input::old('client') ? Input::old('client') : $clientPublicId,
            'invoicePublicId' => Input::old('invoice') ? Input::old('invoice') : $invoicePublicId,
            'invoice' => null,
            'invoices' => Invoice::scope()->where('is_recurring', '=', false)->where('is_quote', '=', false)
                            ->where('invoice_status_id', '<', '5')->with('client', 'invoice_status')->orderBy('invoice_number')->get(),
            'payment' => null, 
            'method' => 'POST', 
            'url' => "payments", 
            'title' => trans('texts.new_payment'),
            //'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
            'paymentTypes' => PaymentType::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
            'clients' => Client::scope()->with('contacts')->orderBy('name')->get());

        return View::make('payments.edit', $data);
    }

    public function edit($publicId)
    {
        $payment = Payment::scope($publicId)->firstOrFail();        
        $payment->payment_date = Utils::fromSqlDate($payment->payment_date);

        $data = array(
            'client' => null,
            'invoice' => null,
            'invoices' => Invoice::scope()->where('is_recurring', '=', false)->where('is_quote', '=', false)
                            ->with('client', 'invoice_status')->orderBy('invoice_number')->get(),
            'payment' => $payment, 
            'method' => 'PUT', 
            'url' => 'payments/' . $publicId, 
            'title' => 'Edit Payment',
            //'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
            'paymentTypes' => PaymentType::remember(DEFAULT_QUERY_CACHE)->orderBy('id')->get(),
            'clients' => Client::scope()->with('contacts')->orderBy('name')->get());
        return View::make('payments.edit', $data);
    }

    
    private function createPayment($invitation, $ref, $payerId = null)
    {
        $invoice = $invitation->invoice;
        $accountGateway = $invoice->client->account->account_gateways[0];

        if ($invoice->account->account_key == NINJA_ACCOUNT_KEY)
        {
            $account = Account::find($invoice->client->public_id);
            $account->pro_plan_paid = date_create()->format('Y-m-d');
            $account->save();
        }
        
        if ($invoice->is_quote)
        {
            $invoice = $this->invoiceRepo->cloneInvoice($invoice, $invoice->id);
        }
        
        $payment = Payment::createNew($invitation);
        $payment->invitation_id = $invitation->id;
        $payment->account_gateway_id = $accountGateway->id;
        $payment->invoice_id = $invoice->id;
        $payment->amount = $invoice->amount;            
        $payment->client_id = $invoice->client_id;
        $payment->contact_id = $invitation->contact_id;
        $payment->transaction_reference = $ref;
        $payment->payment_date = date_create()->format('Y-m-d');
        
        if ($payerId)
        {
            $payment->payer_id = $payerId;                
        }
        
        $payment->save();
        
        Event::fire('invoice.paid', $payment);
        
        return $payment;
    }

    public function store()
    {
        return $this->save();
    }

    public function update($publicId)
    {
        return $this->save($publicId);
    }

    private function save($publicId = null)
    {
        if ($errors = $this->paymentRepo->getErrors(Input::all())) 
        {
            $url = $publicId ? 'payments/' . $publicId . '/edit' : 'payments/create';
            return Redirect::to($url)
                ->withErrors($errors)
                ->withInput();
        } 
        else 
        {            
            $this->paymentRepo->save($publicId, Input::all());

            Session::flash('message', trans('texts.created_payment'));
            return Redirect::to('clients/' . Input::get('client'));
        }
    }

    public function bulk()
    {
        $action = Input::get('action');
        $ids = Input::get('id') ? Input::get('id') : Input::get('ids');
        $count = $this->paymentRepo->bulk($ids, $action);

        if ($count > 0)
        {
            $message = Utils::pluralize($action.'d_payment', $count);            
            Session::flash('message', $message);
        }
        
        return Redirect::to('payments');
    }
}
