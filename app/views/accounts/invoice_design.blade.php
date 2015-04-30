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
{{ Former::legend('panel_settings') }}
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

      {{ Former::legend('Conceptos') }}

      {{ Former::checkbox('hide_quantity')->text(trans('texts.hide_quantity_help')) }}


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