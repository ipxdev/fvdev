<?php

class BranchController extends \BaseController {

  public function getDatatable()
  {
    $query = DB::table('branches')
                ->where('branches.account_id', '=', Auth::user()->account_id)
                ->where('branches.deleted_at', '=', null)
                ->where('branches.public_id', '>', 0)
                ->select('branches.public_id', 'branches.name','branches.activity_pri', 'branches.address1', 'branches.address2', 'branches.postal_code');


    return Datatable::query($query)
      ->addColumn('name', function($model) { return link_to('branches/' . $model->public_id . '/edit', $model->name); })
      ->addColumn('activity_pri', function($model) { return nl2br(Str::limit($model->activity_pri, 100)); })
      ->addColumn('address1', function($model) { return nl2br(Str::limit($model->address2, 60)).', '.nl2br(Str::limit($model->address1, 40)); })
      ->addColumn('postal_code', function($model) { return nl2br(Str::limit($model->postal_code, 30)); })
      ->addColumn('dropdown', function($model) 
      { 
        return '<div class="btn-group tr-action" style="visibility:hidden;">
            <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
              '.trans('texts.select').' <span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
            <li><a href="' . URL::to('branches/'.$model->public_id) . '/edit">'.uctrans('texts.edit_branch').'</a></li>                
            <li class="divider"></li>
            <li><a href="' . URL::to('branches/'.$model->public_id) . '/archive">'.uctrans('texts.archive_branch').'</a></li>
          </ul>
        </div>';
      })       
      ->orderColumns(['name', 'address1'])
      ->make();           
  }

  public function edit($publicId)
  {
    $branch = Branch::scope($publicId)->firstOrFail();

    $data = [
      'showBreadcrumbs' => false,
      'branch' => $branch,
      'method' => 'PUT', 
      'aux' => 'yes',
      'url' => 'branches/' . $publicId, 
      'title' => trans('texts.edit_branch')
    ];

    $data = array_merge($data, self::getViewModel());     
    return View::make('accounts.branch', $data);   
  }

  public function create()
  {
    $data = [
      'showBreadcrumbs' => false,
      'branch' => null,
      'method' => 'POST',
      'aux' => 'no',
      'url' => 'branches', 
      'title' => trans('texts.create_branch')
    ];
    $data = array_merge($data, self::getViewModel()); 
    return View::make('accounts.branch', $data);       
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

  private function save($branchPublicId = false)
  {

    $error = false;
    $var1 = 'NÚMERO DE TRAMITE';
    $var2 = 'NÚMERO DE AUTORIZACIÓN';

    if(Input::file('dosage'))
    {
      $file = Input::file('dosage');
      $name = $file->getRealPath();
      $i = 0;
      $file = fopen($name, "r");
      while(!feof($file))
      {
        $process1 = fgets($file);
        if($i =='0')
        {
          $process2 = explode(":", $process1);
          $result1 = $process2[0];
          if(strcmp($result1, $var1) !== 0){$error=1;}
        }
        if($i =='2')
        {
          $process2 = explode(":", $process1);
          $result2 = $process2[0];
          if(strcmp($result2, $var2) !== 0){$error=1;}
        }
        $i++;
      }
      fclose($file);
    }

		if ($error ==1) 
		{
          Session::flash('error', 'Arhivo inválido');
				  $url = $branchPublicId ? 'branches/' . $branchPublicId . '/edit' : 'branches/create';
          return Redirect::to($url);
		} 
		else 
		{

		    if ($branchPublicId)
		    {
		      $branch = Branch::scope($branchPublicId)->firstOrFail();
		    }
		    else
		    {
		      $branch = Branch::createNew();
		    }

		    $branch->name = trim(Input::get('name'));
        $branch->aux2 = trim(Input::get('aux2'));
		    $branch->address1 = trim(Input::get('address1'));
		    $branch->address2 = trim(Input::get('address2'));
		    $branch->city = trim(Input::get('city'));
		    $branch->state = trim(Input::get('state'));
		    $branch->postal_code = trim(Input::get('postal_code'));
		    $branch->country_id = Input::get('country_id') ? Input::get('country_id') : null;  
		    $branch->industry_id = Input::get('industry_id') ? Input::get('industry_id') : null;


        $deadline = Input::get('deadline');

        $branch->deadline = trim(DateTime::createFromFormat('Y-m-d', $deadline));

        if(Input::file('dosage'))
        {
          $file = Input::file('dosage');
          $name = $file->getRealPath();
        
          $i = 0;
          $file = fopen($name, "r");
          while(!feof($file))
          {
            $process1 = fgets($file);
            if($i =='0')
            {
              $process2 = explode(":", $process1);
              $result1 = $process2[1];
            }
            if($i =='2')
            {
              $process2 = explode(":", $process1);
              $result2 = $process2[1];
            }
            if($i =='6')
            {
              $result3 = $process1;
            }
            $i++;
          }
          fclose($file);

          $branch->aux1 = trim($result1);
          $branch->number_autho = trim($result2);
          $branch->key_dosage = trim($result3);

        }
		    $branch->activity_pri = trim(Input::get('activity_pri'));      
		    $branch->activity_sec1 = trim(Input::get('activity_sec1'));
		    $branch->law = trim(Input::get('law'));

        $branch->invoice_number_counter = 1;

		    $branch->save();

		    $message = $branchPublicId ? trans('texts.updated_branch') : trans('texts.created_branch');
		    Session::flash('message', $message);
        
        Session::flash('message', $message);

		    return Redirect::to('company/branches');    
		}
  }

  public function archive($publicId)
  {
    $branch = Branch::scope($publicId)->firstOrFail();
    $branch->delete();

    Session::flash('message', trans('texts.archived_branch'));
    return Redirect::to('company/branches');        
  }

}