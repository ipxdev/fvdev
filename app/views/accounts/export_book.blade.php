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


{{-- Former::open_for_files('company/import_map')->addClass('col-md-10 col-md-offset-1') --}}
{{-- Former::legend('import_clients') --}}
  <div class="row" style="min-height:20px">
    <div class="col-md-4">
    {{-- Former::file('file')->label('') --}}
    </div>
    <div class="col-md-6">
    {{-- Former::actions( Button::lg_info_submit(trans('texts.upload_import'))->append_with_icon('open') ) --}}
    </div>
  </div>
  {{-- Former::legend('') --}}
  {{-- Former::close() --}}

{{-- Former::open('company/export')->addClass('col-md-9 col-md-offset-1') --}}
{{-- Former::legend('export_clients') --}}
{{-- Former::actions( Button::lg_primary_submit(trans('texts.download'))->append_with_icon('download-alt') ) --}}
{{-- Former::close() --}}

{{ Former::open('company/export')->addClass('col-md-10 col-md-offset-1') }}
{{ Former::legend('export_booksale') }}
  <div class="row" style="min-height:20px">
    <div class="col-md-6">
{{ Former::text('invoice_date')->data_bind("datePicker: invoice_date, valueUpdate: 'afterkeydown',")
   ->data_date_format('yyyy-mm')->append('<i class="glyphicon glyphicon-calendar" onclick="toggleDatePicker(\'invoice_date\')"></i>') }}            
{{ Former::actions( Button::lg_primary_submit(trans('texts.download'))->append_with_icon('download-alt') ) }}
    </div>
  </div>
{{ Former::close() }}

<p>&nbsp;</p>
<p>&nbsp;</p>

{{-- Former::open('company/cancel_account')->addClass('col-md-9 col-md-offset-1 cancel-account') --}}
{{-- Former::legend('cancel_account') --}}
{{-- Former::actions( Button::lg_danger_button(trans('texts.cancel_account'), ['onclick' => 'showConfirm()'])->append_with_icon('trash') ) --}}

<!-- <div class="modal fade" id="confirmCancelModal" tabindex="-1" role="dialog" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
  <div class="modal-dialog" style="min-width:150px">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="confirmCancelModalLabel">{{ trans('texts.cancel_account') }}</h4>
      </div>

      <div style="background-color: #fff; padding-left: 16px; padding-right: 16px">
        &nbsp;<p>{{ trans('texts.cancel_account_message') }}</p>&nbsp;
      </div>

      <div class="modal-footer" style="margin-top: 0px">
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('texts.go_back') }}</button>
        <button type="button" class="btn btn-primary" onclick="confirmCancel()">{{ trans('texts.cancel_account') }}</button>         
      </div>

    </div>
  </div>
</div> -->

{{-- Former::close() --}}  


<script type="text/javascript">

  // function showConfirm() {
  //   $('#confirmCancelModal').modal('show'); 
  // }

  // function confirmCancel() {
  //   $('form.cancel-account').submit();
  // }

    $('#invoice_date').datepicker({
      minViewMode: 1,
      language: "es"
  });

</script>

@stop