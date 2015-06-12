@extends('accounts.nav_advanced')

@section('head')
  @parent

@stop

@section('content') 

@if (Auth::user()->confirmed)
{{ Former::legend('panel_settings') }}
@else
{{ Former::legend('Perfil de la Empresa') }}
@endif

  @parent

	{{ Former::open_for_files()->addClass('col-md-10 col-md-offset-1 warn-on-exit')->rules(array(
  		'name' => 'required',
  		'email' => 'email|required',
  		'nit' => 'required|Numeric',
  		'username' => 'required',
  		'work_phone' => 'required',
  		'address1' => 'required',
  		'address2' => 'required',
  		'country_id' => 'required',
  		'first_name' => 'required',
  		'last_name' => 'required',
  		'phone' => 'required'
	)) }}

	{{ Former::populate($account) }}
	@if ($showUser)
		{{ Former::populateField('first_name', $account->users()->first()->first_name) }}
		{{ Former::populateField('last_name', $account->users()->first()->last_name) }}
		{{ Former::populateField('email', $account->users()->first()->email) }}	
		{{-- Former::populateField('username', $account->users()->first()->username) --}}
<?php     
	$aux = $account->users()->first()->username;
	if (strpos($aux, "@")) {
   	 $aux2 = explode("@", $aux);
	}
	else{
		$aux2[0]='';
	}
?>
		{{ Former::populateField('username', $aux2[0]) }}		
		{{ Former::populateField('phone', $account->users()->first()->phone) }}
	@endif
	
	<div class="row">
		<div class="col-md-6">

			{{ Former::legend('details') }}
			{{ Former::text('nit')->label('NIT (*)') }}
			{{ Former::text('name')->label('EMPRESA (*)') }}
            {{-- Former::text('vat_number') --}}
			{{-- Former::text('work_email') --}}
			{{ Former::text('work_phone')->label('Teléfono (*)') }}

			{{-- Former::select('size_id')->addOption('','')
				->fromQuery($sizes, 'name', 'id') --}}
			{{-- Former::select('industry_id')->addOption('','')
				->fromQuery($industries, 'name', 'id') --}}

			{{ Former::legend('address')  }}
			{{ Former::select('country_id')->addOption('','')->label('ciudad  (*)')
				->fromQuery($countries, 'name', 'id') }}
			{{ Former::text('address2')->label('Dirección (*)') }}
			{{ Former::text('address1')->label('Zona/Barrio (*)') }}
			
			{{-- Former::text('city') --}}
			{{-- Former::text('state') --}}
			{{-- Former::text('postal_code') --}}


		</div>
	
		<div class="col-md-5 col-md-offset-1">		

			@if ($showUser)
				{{ Former::legend('Administrador') }}
				{{ Former::text('first_name')->label('Nombre(s) (*)') }}
				{{ Former::text('last_name')->label('Apellidos (*)') }}
				{{ Former::text('email')->label('Correo electrónico (*)') }}
				{{ Former::text('username')->label('nombre de Usuario (*)') }}
				{{ Former::text('phone')->label('Celular (*)') }}
			@endif

			{{-- Former::legend('localization') --}}
			{{-- Former::select('language_id')->addOption('','')
				->fromQuery($languages, 'name', 'id') --}}			
			{{-- Former::select('currency_id')->addOption('','')
				->fromQuery($currencies, 'name', 'id') --}}			
			{{-- Former::select('timezone_id')->addOption('','')
				->fromQuery($timezones, 'location', 'id') --}}
			{{-- Former::select('date_format_id')->addOption('','')
				->fromQuery($dateFormats, 'label', 'id') --}}
			{{-- Former::select('datetime_format_id')->addOption('','')
				->fromQuery($datetimeFormats, 'label', 'id') --}}


		</div>
	</div>
	
	<center>
		{{ Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk') }}
	</center>
	@if (Auth::user()->isPro())
	<script>
    $(function() {   
    	 $( "#nit" ).prop( "disabled", true );
    	 $( "#name" ).prop( "disabled", true );
    });
	</script>	
	@endif

	{{ Former::close() }}


	<script type="text/javascript">

		$(function() {
			$('#country_id').combobox();
		});
		
	</script>

@stop