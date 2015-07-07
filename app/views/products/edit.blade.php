@extends('header')


@section('onReady')
	$('input#notes').focus();
@stop

@section('content')
<div class="row">

	{{ Former::open($url)->addClass('col-md-10 col-md-offset-1 warn-on-exit')->method($method)->rules(array(
  		'product_key' => 'required|match:/[a-zA-Z0-9.-]+/|min:3', 
  		'notes' => 'required|min:3', 
  		'cost' => 'cost|required|Numeric', 
  	)); }}

	@if ($product)
    	{{ Former::populate($product) }}
	@endif
<hr>
	<div class="row">
		<div class="col-md-6">

		{{ Former::legend('datos Producto') }}
      	{{ Former::text('product_key')->label('texts.product_cod')->title('Solo se acepta Letras, Números y guión(-).') }}
      	{{ Former::textarea('notes')->label('texts.notes') }}

      	{{ Former::text('cost')->label('texts.cost')->title('Solo se acepta números. Ejem: 500.00') }}
      


		</div>
		<div class="col-md-6">

		{{ Former::legend('Categoria') }}
    	{{ Former::select('category_id')->label(' ')->fromQuery($categories, 'name', 'id') }}

		</div>
	</div>
	
<hr>

	<center class="buttons">
	
    {{ Button::lg_default_link('products/' . ($product ? $product->public_id : ''), 'Cancelar')->append_with_icon('remove-circle'); }}
	
	{{ Button::lg_primary_submit_success('Guardar')->append_with_icon('floppy-disk') }}

	</center>

	{{ Former::close() }}
</div>
@stop