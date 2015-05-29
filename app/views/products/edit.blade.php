@extends('header')


@section('onReady')
	$('input#notes').focus();
@stop

@section('content')
<div class="row">

	{{ Former::open($url)->addClass('col-md-12 warn-on-exit')->method($method)->rules(array(
  		'cost' => 'cost|required|Numeric', 
  	)); }}

	@if ($product)
    	{{ Former::populate($product) }}
	@endif

	<div class="row">
		<div class="col-md-6">

		{{ Former::legend('datos Producto') }}
      	{{ Former::text('product_key')->label('texts.product_cod') }}
      	{{ Former::textarea('notes')->label('texts.notes') }}

      	{{ Former::text('cost')->label('texts.cost') }}
      


		</div>
		<div class="col-md-6">
    	{{ Former::select('category_id')->addOption('','')->label('Categoria')
          ->fromQuery($categories, 'name', 'id') }}

		</div>
	</div>


	<center class="buttons">
		{{ Button::lg_primary_submit_success('Guardar')->append_with_icon('floppy-disk') }}
    {{ Button::lg_default_link('products/' . ($product ? $product->public_id : ''), 'Cancelar')->append_with_icon('remove-circle'); }}
	</center>

	{{ Former::close() }}
</div>
@stop