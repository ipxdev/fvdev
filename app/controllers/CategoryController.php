<?php

class CategoryController extends \BaseController {

  public function getDatatable()
  {
    $query = DB::table('categories')
                ->where('categories.account_id', '=', Auth::user()->account_id)
                ->where('categories.deleted_at', '=', null)
                ->where('categories.public_id', '>', 0)
                ->select('categories.public_id', 'categories.name' );


    return Datatable::query($query)
      ->addColumn('name', function($model) { return link_to('categories/' . $model->public_id . '/edit', $model->name); })
      ->addColumn('dropdown', function($model) 
      { 
        return '<div class="btn-group tr-action" style="visibility:hidden;">
            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
              '.trans('texts.select').' <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
            <li><a href="' . URL::to('categories/'.$model->public_id) . '/edit">'.uctrans('texts.edit_category').'</a></li>                
            <li class="divider"></li>
            <li><a href="' . URL::to('categories/'.$model->public_id) . '/archive">'.uctrans('texts.archive_category').'</a></li>
          </ul>
        </div>';
      })       
      ->orderColumns(['name'])
      ->make();           
  }

  public function edit($publicId)
  {
    $category = Category::scope($publicId)->firstOrFail();

    $data = [
      'showBreadcrumbs' => false,
      'category' => $category,
      'method' => 'PUT', 
      'aux' => 'yes',
      'url' => 'categories/' . $publicId, 
      'title' => trans('texts.edit_category')
    ];

    $data = array_merge($data, self::getViewModel());     
    return View::make('accounts.category', $data);   
  }

  public function create()
  {
    $data = [
      'showBreadcrumbs' => false,
      'category' => null,
      'method' => 'POST',
      'aux' => 'no',
      'url' => 'categories', 
      'title' => trans('texts.create_category')
    ];
    $data = array_merge($data, self::getViewModel()); 
    return View::make('accounts.category', $data);       
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

  private function save($categoryPublicId = false)
  {

	    if ($categoryPublicId)
	    {
	      $category = Category::scope($categoryPublicId)->firstOrFail();
	    }
	    else
	    {
	      $category = Category::createNew();
	    }

	    $category->name = trim(Input::get('name'));

	    $category->save();

	    $message = $categoryPublicId ? trans('texts.updated_category') : trans('texts.created_category');
	    Session::flash('message', $message);
      
      Session::flash('message', $message);

	    return Redirect::to('company/categories');  

  }

  public function archive($publicId)
  {
    $category = Category::scope($publicId)->firstOrFail();
    $category->delete();

    Session::flash('message', trans('texts.archived_category'));
    return Redirect::to('company/categories');        
  }

}