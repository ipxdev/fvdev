@extends('accounts.nav_advanced')

@section('content') 

@if (!Auth::user()->confirmed)

<hr>
<div class="btn-group btn-group-justified" role="group" >
  <div class="btn-group" role="group">
        @if(Auth::user()->account->getOp1())
          <a href="{{ URL::to('company/details') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover2" >              
          <b>Paso 1 </b><br> Perfil de la Empresa
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>
        @else          
          <a href="{{ URL::to('company/details') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover1" >  
          <b>Paso 1 </b></span><br> Perfil de la Empresa
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button> </a>             
        @endif
    
  </div>
  <div class="btn-group" role="group">
      @if(Auth::user()->account->getOp2()) 
        <a href="{{ URL::to('company/branches') }}" style="color:#333333;text-decoration:none;">
      <button type="button" class="btn btn-default ipxhover2">
        <b>Paso 2 </b><br>  Datos de Sucursal
        <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>
      @else      
        <a href="{{ URL::to('company/branches') }}" style="color:#333333;text-decoration:none;">
        <button type="button" class="btn btn-default ipxhover3">
        <b>Paso 2 </b><br> Datos de Sucursal
        <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>              
      @endif
    </button>
  </div>
  <div class="btn-group" role="group">
        @if(Auth::user()->account->getOp3())
          <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover2">
      <b>Paso 3 </b><br> Cargado del Logo
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a> 
        @else
            @if(Auth::user()->account->getOp2())
              <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;">
              <button type="button" class="btn btn-default ipxhover3">     
              <b>Paso 3 </b><br> Cargado del Logo
              <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
              </button></a>  
            @else 
              <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;">
              <button type="button" class="btn btn-default ipxhover1" disabled>     
              <b>Paso 3 </b><br> Cargado del Logo
              <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
              </button></a> 
            @endif           
        @endif
  </div>
</div>
<hr>

@endif

@if (Auth::user()->confirmed)
{{ Former::legend('panel_settings') }}
@endif

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


  <center>
  @if (!Auth::user()->confirmed)
    @if(Auth::user()->account->getOp2()) 
    <a href="{{ URL::to('company/invoice_design') }}">
    <button type="button" class="btn lg-success-submit ipxhover2" >              
    Siguiente<span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
    </button>
    </a>
    @else
    <a href="{{ URL::to('company/invoice_design') }}">
    <button type="button" class="btn lg-success-submit ipxhover2" disabled>              
    Siguiente<span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
    </button>
    </a>
    @endif
  @endif 
  </center>
@stop