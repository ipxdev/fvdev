@extends('accounts.nav_advanced')

@section('head')
  @parent

@stop

@section('content') 

@if (!Auth::user()->confirmed)

<hr>
<div class="btn-group btn-group-justified" role="group" >
  <div class="btn-group" role="group">
        @if(Auth::user()->account->getOp1())
       	  <a href="{{ URL::to('company/details') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover2" >              
          <b>Paso 1 </b><br> Perfil de la Empresa
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>
        @else          
          <a href="{{ URL::to('company/details') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover1" >  
          <b>Paso 1 </b></span><br> Perfil de la Empresa
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button> </a>             
        @endif
    
  </div>
  <div class="btn-group" role="group">
	    @if(Auth::user()->account->getOp2()) 
	      <a href="{{ URL::to('company/branches') }}" style="color:#333333;text-decoration:none;">
		  <button type="button" class="btn btn-default ipxhover2">
	      <b>Paso 2 </b><br>  Datos de Sucursal
	      <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>
	    @else	     
	      <a href="{{ URL::to('company/branches') }}" style="color:#333333;text-decoration:none;">
	      <button type="button" class="btn btn-default ipxhover1">
	      <b>Paso 2 </b><br> Datos de Sucursal
	      <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>              
	    @endif
    </button>
  </div>
  <div class="btn-group" role="group">
        @if(Auth::user()->account->getOp3())
          <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover2">
		  <b>Paso 3 </b><br> Cargado del Logo
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a> 
        @else
          <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;">
          <button type="button" class="btn btn-default ipxhover1">     
          <b>Paso 3 </b><br> Cargado del Logo
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button></a>             
        @endif
  </div>
</div>
<hr>

@endif

@if (Auth::user()->confirmed)
{{ Former::legend('panel_settings') }}
@else
{{ Former::legend('Perfil de la Empresa') }}
@endif

  @parent

	{{ Former::open_for_files()->addClass('col-md-12 warn-on-exit')->rules(array(
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
  		'phone' => 'required',
  		'password' => 'required',
  		'password_confirmation' => 'required'
  		
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
	
		<div class="col-md-6">		

			@if ($showUser)
				{{ Former::legend('Administrador') }}
				{{ Former::text('first_name')->label('Nombre(s) (*)') }}
				{{ Former::text('last_name')->label('Apellidos (*)') }}
				{{ Former::text('phone')->label('Celular (*)') }}
				{{ Former::text('email')->label('Correo electrónico (*)') }}

				{{ Former::legend('Datos de Ingreso') }}
				{{ Former::text('username')->label('nombre de Usuario (*)') }}
				{{ Former::password('password')->label('contraseña (*)') }}        
				{{ Former::password('password_confirmation')->label('Repertir contraseña (*)') }}        

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