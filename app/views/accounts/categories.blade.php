@extends('accounts.nav_advanced')

@section('content') 

  {{ Former::legend('panel_settings') }}

  @parent
  <div class="row">
    <div class="col-md-6">  
      {{ Former::legend('Gestión de Categorias') }}
    </div>
    <div class="col-md-3">  
    </div>
    <div class="col-md-3">  
  {{ Button::success_link(URL::to('categories/create'), trans("texts.create_category"), array('class' => 'pull-right'))->append_with_icon('plus-sign') }} 
    </div>
  </div>


  {{ Datatable::table()   
      ->addColumn(
        trans('texts.name'),
        trans('texts.action'))
      ->setUrl(url('api/categories/'))      
      ->setOptions('sPaginationType', 'bootstrap')
      ->setOptions('bFilter', false)      
      ->setOptions('bAutoWidth', false)      
      ->setOptions('aoColumns', [[ "sWidth"=> "70%" ], ["sWidth"=> "30%" ]])      
      ->setOptions('aoColumnDefs', [['bSortable'=>false, 'aTargets'=>[2]]])
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