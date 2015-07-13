<?php 

use ninja\repositories\CreditRepository;

class CreditController extends \BaseController {

    protected $creditRepo;

    public function __construct(CreditRepository $creditRepo)
    {
        parent::__construct();

        $this->creditRepo = $creditRepo;
    }   

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        return View::make('list', array(
            'entityType'=>ENTITY_CREDIT, 
            'title' => trans('texts.credits'),
            'columns'=>Utils::trans(['checkbox', 'client', 'credit_amount', 'credit_balance', 'credit_date', 'private_notes', 'action'])
        ));
    }

    public function getDatatable($clientPublicId = null)
    {
        $credits = $this->creditRepo->find($clientPublicId, Input::get('sSearch'));

        $table = Datatable::query($credits);        

        if (!$clientPublicId) 
        {
            $table->addColumn('checkbox', function($model) { return '<input type="checkbox" name="ids[]" value="' . $model->public_id . '">'; })
                  ->addColumn('client_name', function($model) { return link_to('clients/' . $model->client_public_id, Utils::getClientDisplayName($model)); });
        }
        
        return $table->addColumn('amount', function($model){ return Utils::formatMoney($model->amount, 1); })
            ->addColumn('balance', function($model){ return Utils::formatMoney($model->balance, 1); })
            ->addColumn('credit_date', function($model) { return Utils::fromSqlDate($model->credit_date); })
            ->addColumn('private_notes', function($model) { return $model->private_notes; })
            ->addColumn('dropdown', function($model) 
            { 
                return '<div class="btn-group tr-action" style="visibility:hidden;">
                            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
                                '.trans('texts.select').' <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                            <li><a href="javascript:archiveEntity(' . $model->public_id. ')">'.trans('texts.archive_credit').'</a></li>
                            <li><a href="javascript:deleteEntity(' . $model->public_id. ')">'.trans('texts.delete_credit').'</a></li>                          
                          </ul>
                        </div>';
            })         
            ->make();       
    }


    public function create($clientPublicId = 0)
    {       
        $data = array(
            'clientPublicId' => Input::old('client') ? Input::old('client') : $clientPublicId,
            //'invoicePublicId' => Input::old('invoice') ? Input::old('invoice') : $invoicePublicId,
            'credit' => null, 
            'method' => 'POST', 
            'url' => 'credits', 
            'title' => trans('texts.new_credit'),
            //'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
            //'invoices' => Invoice::scope()->with('client', 'invoice_status')->orderBy('invoice_number')->get(),
            'clients' => Client::scope()->with('contacts')->orderBy('name')->get());

        return View::make('credits.edit', $data);
    }

    public function edit($publicId)
    {
        $credit = Credit::scope($publicId)->firstOrFail();
        $credit->credit_date = Utils::fromSqlDate($credit->credit_date);

        $data = array(
            'client' => null,
            'credit' => $credit, 
            'method' => 'PUT', 
            'url' => 'credits/' . $publicId, 
            'title' => 'Edit Credit',
            //'currencies' => Currency::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
            'clients' => Client::scope()->with('contacts')->orderBy('name')->get());
        return View::make('credit.edit', $data);
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
        $rules = array(
            'client' => 'required',
            'amount' => 'required|positive',
        );

        $validator = Validator::make(Input::all(), $rules);

        if ($validator->fails()) 
        {
            $url = $publicId ? 'credits/' . $publicId . '/edit' : 'credits/create';
            return Redirect::to($url)
                ->withErrors($validator)
                ->withInput();
        } 
        else 
        {            
            $this->creditRepo->save($publicId, Input::all());

            $message = trans('texts.created_credit');
            Session::flash('message', $message);
            return Redirect::to('clients/' . Input::get('client'));
        }
    }

    public function bulk()
    {
        $action = Input::get('action');
        $ids = Input::get('id') ? Input::get('id') : Input::get('ids');        
        $count = $this->creditRepo->bulk($ids, $action);

        if ($count > 0)
        {
            $message = Utils::pluralize($action.'d_credit', $count);            
            Session::flash('message', $message);
        }

        return Redirect::to('credits');
    }
}
