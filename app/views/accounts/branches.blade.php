@extends('accounts.nav_advanced')

@section('content') 

  {{ Former::legend('panel_settings') }}

  @parent
  <div class="row">
    <div class="col-md-6">  
      {{ Former::legend('Gestión de Sucursales') }}
    </div>
    <div class="col-md-3">  
    </div>
    <div class="col-md-3">  
  {{ Button::success_link(URL::to('branches/create'), trans("texts.create_branch"), array('class' => 'pull-right'))->append_with_icon('plus-sign') }} 
    </div>
  </div>


  {{ Datatable::table()   
      ->addColumn(
        trans('texts.name'),
        trans('texts.activity_eco'),
        trans('texts.zone_direccion'),
        trans('texts.work_phone'),
        trans('texts.action'))
      ->setUrl(url('api/branches/'))      
      ->setOptions('sPaginationType', 'bootstrap')
      ->setOptions('bFilter', false)      
      ->setOptions('bAutoWidth', false)      
      ->setOptions('aoColumns', [[ "sWidth"=> "20%" ], [ "sWidth"=> "26%" ], [ "sWidth"=> "26%" ], ["sWidth"=> "14%"], ["sWidth"=> "14%" ]])      
      ->setOptions('aoColumnDefs', [['bSortable'=>false, 'aTargets'=>[3]]])
      ->render('datatable') }}

  <script>
  window.onDatatableReady = function() {        
    $('tbody tr').mouseover(function() {
      $(this).closest('tr').find('.tr-action').css('visibility','visible');
    }).mouseout(function() {
      $dropdown = $(this).closest('tr').find('.tr-action');
      if (!$dropdown.hasClass('open')) {
        $dropdown.css('visibility','hidden');
      }     
    });
  } 
  </script>  


@stop