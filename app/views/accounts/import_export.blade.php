@extends('accounts.nav_advancedie')

@section('head')
  @parent

    <script src="{{ asset('js/pdf_viewer.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/compatibility.js') }}" type="text/javascript"></script>
@stop

@section('content') 
{{ Former::legend('panel_ie') }}
  @parent

<p>&nbsp;</p>

{{ Former::open_for_files('company/import_map_invoice')->addClass('col-md-10 col-md-offset-1') }}
{{ Former::legend('import_invoices') }}
  <div class="row" style="min-height:20px">
    <div class="col-md-4">
    {{ Former::file('file')->label('') }}
    </div>
    <div class="col-md-6">
    {{ Former::actions( Button::lg_info_submit(trans('texts.upload_import'))->append_with_icon('open') ) }}
    </div>
  </div>
{{ Former::legend('') }}
{{ Former::close() }}


<script type="text/javascript">

    $('#invoice_date').datepicker({
      minViewMode: 1,
      language: "es"
  });

</script>

@stop