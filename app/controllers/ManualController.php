<?php

class ManualController extends \BaseController {

  public function getDatatable()
  {
    $query = DB::table('book_sales')
                ->where('book_sales.account_id', '=', Auth::user()->account_id)
                ->where('book_sales.deleted_at', '=', null)
                ->select('book_sales.id', 'book_sales.invoice_number','book_sales.client_nit','book_sales.client_name','book_sales.amount');


    return Datatable::query($query)
      ->addColumn('invoice_number', function($model) { return link_to('manuals/' . $model->id . '/edit', $model->invoice_number); })
      ->addColumn('dropdown', function($model) 
      { 
        return '<div class="btn-group tr-action" style="visibility:hidden;">
            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
              '.trans('texts.select').' <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
            <li><a href="' . URL::to('manuals/'.$model->id) . '/edit">'.uctrans('texts.edit_manual').'</a></li>                
            <li class="divider"></li>
            <li><a href="' . URL::to('manuals/'.$model->id) . '/archive">'.uctrans('texts.archive_manual').'</a></li>
          </ul>
        </div>';
      })       
      ->orderColumns(['invoice_number'])
      ->make();           
  }

  public function edit($publicId)
  {
    $data = [
      'showBreadcrumbs' => false,
      'manual' => BookSale::scope($publicId)->firstOrFail(),
      'method' => 'PUT', 
      'url' => 'manuals/' . $publicId, 
      'title' => trans('texts.edit_manual')
    ];

    $data = array_merge($data, self::getViewModel());     
    return View::make('accounts.manual', $data);   
  }

  public function create()
  {
    $data = [
      'showBreadcrumbs' => false,
      'manual' => null,
      'method' => 'POST',
      'url' => 'manuals', 
      'title' => trans('texts.create_manual')
    ];
    $data = array_merge($data, self::getViewModel()); 
    return View::make('accounts.manual', $data);       
  }

  private static function getViewModel()
  {
    return [   

      'countries' => Country::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),
      'industries' => Industry::remember(DEFAULT_QUERY_CACHE)->orderBy('name')->get(),        
      
    ];
  }

  public function store()
  {
    return $this->save();
  }

  public function update($publicId)
  {
    return $this->save($publicId);
  }  

  private function save($manualPublicId = false)
  {

  		$rules = array(
			'name' => 'required'
		);
		$messages = array(
    	'unique' => 'El Nombre de Sucursal ya ha sido registrado.',
		);
		$validator = Validator::make(Input::all(), $rules, $messages);
		if ($validator->fails()) 
		{
			$url = $manualPublicId ? 'manuals/' . $manualPublicId . '/edit' : 'manuals/create';
			return Redirect::to($url)
				->withErrors($validator);
				
		} 
		else 
		{

		    if ($manualPublicId)
		    {
		      $manual = BookSale::scope($manualPublicId)->firstOrFail();
		    }
		    else
		    {
		      $manual = BookSale::createNew();
		    }

		    $manual->name = trim(Input::get('name'));

		    $manual->save();

		    $message = $manualPublicId ? trans('texts.updated_manual') : trans('texts.created_manual');
		    Session::flash('message', $message);

		    return Redirect::to('company/manuals');    
		}
  }

  public function archive($publicId)
  {
    $bmanual = BookSale::scope($publicId)->firstOrFail();
    $manual->delete();

    Session::flash('message', trans('texts.archived_manual'));
    return Redirect::to('company/manuals');        
  }

}