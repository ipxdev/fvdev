@extends('accounts.nav_advanced')

@section('head')
  @parent

    <script src="{{ asset('js/pdf_viewer.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/compatibility.js') }}" type="text/javascript"></script>
    
    <style type="text/css">
    #logo {
        padding-top: 6px;
    }
    input.range { -webkit-appearance: slider-horizontal;  }
    </style>

@stop

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
        <button type="button" class="btn btn-default ipxhover1">
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
          <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover1">     
          <b>Paso 3 </b><br> Cargado del Logo
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>             
        @endif
  </div>
</div>
<hr>

@endif

@if (Auth::user()->confirmed)
{{ Former::legend('panel_settings') }}
@else
{{ Former::legend('Diseño de Factura') }}
@endif

  @parent

  <script>
    var invoiceDesigns = {{ $invoiceDesigns }};
    var branches = {{ $branches }};
    var invoice = {{ json_encode($invoice) }};   

    var xd = invoiceDesigns[0].x;   
    var yd = invoiceDesigns[0].y;

    function getDesignJavascript() {
      // var id = $('#invoice_design_id').val();
      return invoiceDesigns[0].javascript;
    }

    function getLogoJavascript() {
      return invoiceDesigns[0].name; 
    }

    $('#x, #y').change(function() {
      setTimeout(function() {
        refreshPDF();
      }, 1);
    });
    function onItemChange()
      {
        refreshPDF();  
      }


    function getPDFString() {
      var id = $('#branch_id').val();
      // id = id - 1;
      var aux = '0';
      for (i = 0; i < branches.length; i++) { 
        if(branches[i].public_id == id){
          aux = branches[i].public_id;
        }
      }

      var x = $('#x').val();
      var y = $('#y').val();

      $('#x, #y').change(function() {

      xd = x;
      yd = y;
      });

      function getLogoXJavascript() {
        return xd;
      }

      function getLogoYJavascript() {
        return yd;
      }

      invoice.branch_name = branches[aux-1].name;
      invoice.address2 = branches[aux-1].address2;
      invoice.address1 = branches[aux-1].address1;
      invoice.phone = branches[aux-1].postal_code;
      invoice.city = branches[aux-1].city;
      invoice.state = branches[aux-1].state;
      invoice.number_autho = branches[aux-1].number_autho;
      invoice.deadline = branches[aux-1].deadline;
      invoice.activity_pri = branches[aux-1].activity_pri;
      invoice.aux1 = branches[aux-1].aux2;
      


      invoice.is_pro = {{ Auth::user()->isPro() ? 'true' : 'false' }};
      invoice.account.hide_quantity = $('#hide_quantity').is(":checked");
      invoice.account.hide_paid_to_date = $('#hide_paid_to_date').is(":checked");
      NINJA.primaryColor = $('#primary_color').val();
      NINJA.secondaryColor = $('#secondary_color').val();
      var doc = generatePDF(invoice, getDesignJavascript(), getLogoJavascript(), getLogoXJavascript(), getLogoYJavascript(), true);
      if (!doc) {
        return;
      }
      return doc.output('datauristring');
    }
    $(function() {   
      refreshPDF();
    });
  </script> 

{{ Former::open_for_files()->addClass('warn-on-exit')->onchange('refreshPDF()')->rules(['design' => 'required']) }}
{{ Former::populate($account) }}

{{ Former::populateField('hide_quantity', intval($account->hide_quantity)) }}
{{ Former::populateField('x', intval($invoiceDesign->x)) }}
{{ Former::populateField('y', intval($invoiceDesign->y)) }}



  <div class="row">
    <div class="col-md-6">

      @if (Auth::user()->isPro())
        {{ Former::legend('Logo') }}
      @else
        {{ Former::legend('Logo') }}
      @endif

      {{ Former::file('logo')->label('logo')->max(2, 'MB')->accept('image')->inlineHelp(trans('texts.logo_help')) }}

      @if (file_exists($account->getLogoPath()))
        {{ Former::range('x')->label('horizontal')->min(0)->max(160)->step(5)->class('range')->onkeyup('onItemChange()') }}
        {{ Former::range('y')->label('vertical')->min(0)->max(60)->step(3)->class('range') }}
       @endif

      @if (Auth::user()->isPro())
        {{ Former::legend('Nuevo Diseño') }}
      @else
        {{ Former::legend('modificar Diseño') }}
      @endif

      {{ Form::textarea('design', null, ['size' => '10x5']) }}


      <p>&nbsp;</p>
      <p>&nbsp;</p>

      @if (Auth::user()->isPro())
        {{ Former::actions( Button::lg_success_submit(trans('Guardar'))->append_with_icon('floppy-disk') ) }}
      @else
        {{ Former::actions( Button::lg_success_submit(trans('Modificar'))->append_with_icon('floppy-disk') ) }}
      @endif

    </div>

    <div class="col-md-6">

      {{ Former::legend('previsualización ') }}

      {{ Former::select('branch_id')->label('Cambiar Sucursal')->style('display:inline;width:220px')->fromQuery($branches, 'name', 'public_id') }}

      @include('invoices.pdfdesign', ['account' => Auth::user()->account, 'pdfHeight' => 800])

    </div>
  </div>

{{ Former::close() }}


@stop