@extends('header')

@section('content')
	
  	<script>
    var branches = {{ $branches }};
    </script>

	{{ Former::open('company/import_exporti')->addClass('warn-on-exit') }}
	{{ Former::legend('branch_select') }}

	@if ($headers)
  <div class="row">
    <div class="col-md-12">
	      {{ Former::select('branch_id')->label('Sucursal')->style('width:300px')->fromQuery($branches, 'name', 'id') }}
	          </div>
  </div>

  	{{ Former::legend('import_invoices') }}

		<table class="table">
			<thead>
				<tr>
					<th>{{ trans('texts.column') }}</th>
					<th class="col_sample">{{ trans('texts.sample') }}</th>
					<th>{{ trans('texts.import_to') }}</th>
				</tr>	
			</thead>		
		@for ($i=0; $i<count($headers); $i++)
			<tr>
				<td>{{ $headers[$i] }}</td>
				<td class="col_sample">{{ $data[1][$i] }}</td>
				<td>{{ Former::select('map[' . $i . ']')->options($columns, $mapped[$i], true)->raw() }}</td>
			</tr>
		@endfor
		</table>

		<span id="numClients"></span>
	@endif


	{{ Former::actions( Button::lg_primary_submit(trans('texts.import')), '&nbsp;|&nbsp;', link_to('company/import_export', trans('texts.cancel')) ) }}
	{{ Former::close() }}

	<script type="text/javascript">

		$(function() {

			var numClients = {{ count($data) }};
			function setSampleShown() {

					$('.col_sample').show();
					setNumClients(numClients - 1);
				
			}

			function setNumClients(num)
			{
				if (num == 1)
				{
					$('#numClients').html("1 {{ trans('texts.invoice_will_create') }}");
				}
				else
				{
					$('#numClients').html(num + " {{ trans('texts.invoices_will_create') }}");
				}
			}

			$('#header_checkbox').click(setSampleShown);
			setSampleShown();

		});

	</script>

@stop