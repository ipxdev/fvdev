@extends('accounts.nav_advanced')

@section('head')
  @parent

    <script src="{{ asset('js/pdf_viewer.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/compatibility.js') }}" type="text/javascript"></script>
@stop

@section('content') 
{{ Former::legend('panel_settings') }}
  @parent

  <script>
    var invoiceDesigns = {{ $invoiceDesigns }};
    var branches = {{ $branches }};
    var invoice = {{ json_encode($invoice) }};      
      
    function getDesignJavascript() {
      // var id = $('#invoice_design_id').val();
      return invoiceDesigns[0].javascript;

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
      var doc = generatePDF(invoice, getDesignJavascript(), true);
      if (!doc) {
        return;
      }
      return doc.output('datauristring');
    }
    $(function() {   
      refreshPDF();
    });
  </script> 


  <div class="row">
    <div class="col-md-6">

      {{ Former::open()->addClass('warn-on-exit')->onchange('refreshPDF()')->rules(['design' => 'required']) }}
      {{ Former::populate($account) }}
      {{ Former::populateField('hide_quantity', intval($account->hide_quantity)) }}
      {{ Former::populateField('hide_paid_to_date', intval($account->hide_paid_to_date)) }}

      {{ Former::legend('invoice_design') }}

      
      {{-- Former::select('invoice_design_id')->style('display:inline;width:140px')->fromQuery($invoiceDesigns, 'name', 'id') --}}
     
      {{ Former::select('branch_id')->label('Sucursal')->style('display:inline;width:140px')->fromQuery($branches, 'name', 'public_id') }}


      <p>&nbsp;</p>
      <p>&nbsp;</p>

      @if (Auth::user()->isPro())
        {{ Former::legend('Guardar Nuevo Diseño') }}
      @else
        {{ Former::legend('modificar Diseño') }}
      @endif

        {{ Form::textarea('design', null, ['size' => '10x16']) }}

      {{-- Former::checkbox('hide_quantity')->text(trans('texts.hide_quantity_help')) --}}
      {{-- Former::checkbox('hide_paid_to_date')->text(trans('texts.hide_paid_to_date_help')) --}}

      <p>&nbsp;</p>
      <p>&nbsp;</p>

      @if (Auth::user()->isPro())
        {{ Former::actions( Button::lg_success_submit(trans('Guardar'))->append_with_icon('floppy-disk') ) }}
      @else
        {{ Former::actions( Button::lg_success_submit(trans('Modificar'))->append_with_icon('floppy-disk') ) }}

      @endif

      {{ Former::close() }}

    </div>
    <div class="col-md-6">

      @include('invoices.pdfdesign', ['account' => Auth::user()->account, 'pdfHeight' => 800])

    </div>
  </div>

@stop