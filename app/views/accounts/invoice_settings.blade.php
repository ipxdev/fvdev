@extends('accounts.nav_advanced')

@section('content')	
{{ Former::legend('panel_settings') }}
	@parent

	{{ Former::open()->addClass('col-md-8 col-md-offset-2 warn-on-exit') }}
	{{ Former::populate($account) }}
	{{ Former::populateField('custom_invoice_taxes1', intval($account->custom_invoice_taxes1)) }}
	{{ Former::populateField('custom_invoice_taxes2', intval($account->custom_invoice_taxes2)) }}
  	{{-- Former::populateField('share_counter', intval($account->share_counter)) --}}


	{{-- Former::legend('invoice_fields') --}}
	{{-- Former::text('custom_invoice_label1')->label(trans('texts.field_label'))
			->append(Former::checkbox('custom_invoice_taxes1')->raw() . ' ' . trans('texts.charge_taxes')) --}}		
	{{-- Former::text('custom_invoice_label2')->label(trans('texts.field_label'))
			->append(Former::checkbox('custom_invoice_taxes2')->raw() . ' ' . trans('texts.charge_taxes')) --}}			
	<!-- <p>&nbsp;</p> -->

	{{ Former::legend('client_fields') }}
	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Índice</p>
	{{ Former::text('custom_client_label1')->label(trans('texts.field_label')) }}
	{{ Former::legend('') }}

	<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Fechas</p>
	{{ Former::text('custom_client_label11')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label12')->label(trans('texts.field_label')) }}
	{{ Former::legend('') }}
	
	{{ Former::text('custom_client_label2')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label3')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label4')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label5')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label6')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label7')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label8')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label9')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_client_label10')->label(trans('texts.field_label')) }}
	


	<p>&nbsp;</p>

	{{ Former::legend('company_fields') }}
	{{ Former::text('custom_label1')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_value1')->label(trans('texts.field_value')) }}
	<p>&nbsp;</p>
	{{ Former::text('custom_label2')->label(trans('texts.field_label')) }}
	{{ Former::text('custom_value2')->label(trans('texts.field_value')) }}
	<p>&nbsp;</p>

<!-- 	{{ Former::legend('invoice_number') }}
	{{ Former::text('invoice_number_prefix')->label(trans('texts.invoice_number_prefix')) }}
	{{ Former::text('invoice_number_counter')->label(trans('texts.invoice_number_counter')) }}
	<p>&nbsp;</p>
	
	{{ Former::legend('quote_number') }}
	{{ Former::text('quote_number_prefix')->label(trans('texts.quote_number_prefix')) }}
	{{ Former::text('quote_number_counter')->label(trans('texts.quote_number_counter'))
			->append(Former::checkbox('share_counter')->raw()->onclick('setQuoteNumberEnabled()') . ' ' . trans('texts.share_invoice_counter')) }}
	<p>&nbsp;</p> -->


	{{ Former::actions( Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk') ) }}

	{{ Former::close() }}


	<script type="text/javascript">

  // 	function setQuoteNumberEnabled() {
		// 	var disabled = $('#share_counter').prop('checked');
		// 	$('#quote_number_counter').prop('disabled', disabled);
		// 	$('#quote_number_counter').val(disabled ? '' : '{{ $account->quote_number_counter }}');			
		// }

  //   $(function() {       	
  //   	setQuoteNumberEnabled();
  //   });    

	</script>


@stop