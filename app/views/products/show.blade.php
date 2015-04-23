@extends('header')

@section('content') 
	
	
	@if (!$product->trashed())		
	<div class="pull-right">
		{{ Former::open('products/bulk')->addClass('mainForm') }}
		<div style="display:none">
			{{ Former::text('action') }}
			{{ Former::text('id')->value($product->public_id) }}
		</div>

		{{ DropdownButton::normal(trans('texts.edit_product'),
			  Navigation::links(
			    [
			      [trans('texts.edit_product'), URL::to('products/' . $product->public_id . '/edit')],
			      [Navigation::DIVIDER],
			      [trans('texts.archive_product'), "javascript:onArchiveClick()"],
			    ]
			  )
			, ['id'=>'normalDropDown'])->split(); }}	

	    {{ Former::close() }}	

	</div>
	@endif

	<div class="row">

		<div class="col-md-8">
			<table class="table" style="width:100%">
				<tr>
					<td><h3><strong>Nombre Producto </strong> : {{ $product->getDisplayName() }}</h3></td>				
				</tr>
			</table>

			
		</div>

	</div>

	<div class="row">

		<div class="col-md-3">
			<h3>{{ trans('texts.details') }}</h3>
			<p><strong>Código Nº </strong> : {{ $product->getProductKey() }}</p>
			<p><strong>Costo </strong> : {{ $product->getProductCost() }}</p>

		</div>

	</div>

	<p>&nbsp;</p>

	
	<script type="text/javascript">


	$(function() {
		$('#normalDropDown > button:first').click(function() {
			window.location = '{{ URL::to('products/' . $product->public_id . '/edit') }}';
		});

	});

	function onArchiveClick() {
		$('#action').val('archive');
		$('.mainForm').submit();
	}

	function onDeleteClick() {
		if (confirm("{{ trans('texts.are_you_sure') }}")) {
			$('#action').val('delete');
			$('.mainForm').submit();
		}		
	}

	</script>

@stop