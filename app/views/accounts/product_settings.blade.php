@extends('accounts.nav_advanced')
	

@section('content')	
{{ Former::legend('panel_settings') }}

	@parent

  {{ Former::legend('product_config') }}

  {{ Former::open()->addClass('col-md-10 col-md-offset-1 warn-on-exit') }}
  {{ Former::populateField('fill_products', intval($account->fill_products)) }}
  {{ Former::populateField('update_products', intval($account->update_products)) }}

  {{ Former::checkbox('fill_products')->text(trans('texts.fill_products_help')) }}
  {{ Former::checkbox('update_products')->text(trans('texts.update_products_help')) }}
  &nbsp;
  {{ Former::actions( Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk') ) }}
  {{ Former::close() }}


	<script>
    // $(function() {   
    	// $('form.warn-on-exit input').prop('disabled', true);
    // });
	</script>	

	{{ Former::close() }}

@stop