@extends('accounts.nav_advanced')

@section('head')
  @parent

@stop

@section('content') 

@if (!Auth::user()->confirmed)

<br>
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
          <button type="button" class="btn btn-default ipxhover3">  
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
	    	@if(Auth::user()->account->getOp1())
	    	  <a href="{{ URL::to('company/branches') }}" style="color:#333333;text-decoration:none;">	     
		      <button type="button" class="btn btn-default ipxhover1">
		      <b>Paso 2 </b><br> Datos de Sucursal
		      <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
	          </button></a>
         	@else 
	          <button type="button" class="btn btn-default ipxhover1" disabled>
		      <b>Paso 2 </b><br> Datos de Sucursal
		      <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
	          </button> 
          	@endif             
	    @endif
    </button>
  </div>
  <div class="btn-group" role="group">
        @if(Auth::user()->account->getOp3())
          <button type="button" class="btn btn-default ipxhover2">
		  <b>Paso 3 </b><br> Cargado del Logo
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button> 
        @else
          <button type="button" class="btn btn-default ipxhover1" disabled>     
          <b>Paso 3 </b><br> Cargado del Logo
          <span style="margin:3px 0" class="glyphicon glyphicon-chevron-right"> 
          </button>             
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


			{{ Former::checkbox('unipersonal')->label(' ')->text('unipersonal')->data_bind("checked: displayAdvancedOptions") }}

			{{ Former::legend('nombre de Contribuyente')->data_bind("fadeVisible: displayAdvancedOptions") }}
		    {{ Former::text('uniper')->label(' ')->data_bind("Visible:none,fadeVisible: displayAdvancedOptions") }}    


			{{ Former::legend('address')  }}
			{{ Former::select('country_id')->addOption('','')->label('ciudad  (*)')
				->fromQuery($countries, 'name', 'id') }}
			{{ Former::text('address2')->label('Dirección (*)') }}
			{{ Former::text('address1')->label('Zona/Barrio (*)') }}
			{{ Former::text('work_phone')->label('Teléfono (*)') }}			

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

				@if (!Auth::user()->confirmed)
					{{ Former::password('password')->label('contraseña (*)') }}        
					{{ Former::password('password_confirmation')->label('Repertir contraseña (*)') }}      

				@endif 

			@endif

		</div>
	</div>
	
	<center>

	@if (Auth::user()->confirmed)
		{{ Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk') }}
	@else
		{{ Button::lg_success_submit('Siguiente')->append_with_icon('chevron-right') }}
	@endif	
	</center>


  <script type="text/javascript">

  var PlanetsModel = function() {
      
      this.displayAdvancedOptions = ko.observable({{ $account->is_uniper ? 'true' : 'false' }});
   
      // Animation callbacks for the planets list
      this.showPlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }
      this.hidePlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).slideUp(function() { $(elem).remove(); }) }
  };
   
  // Here's a custom Knockout binding that makes elements shown/hidden via jQuery's fadeIn()/fadeOut() methods
  // Could be stored in a separate utility library
  ko.bindingHandlers.fadeVisible = {
      init: function(element, valueAccessor) {
          // Initially set the element to be instantly visible/hidden depending on the value
          var value = valueAccessor();
          $(element).toggle(ko.utils.unwrapObservable(value)); // Use "unwrapObservable" so we can handle values that may or may not be observable
      },
      update: function(element, valueAccessor) {
          // Whenever the value subsequently changes, slowly fade the element in or out
          var value = valueAccessor();
          ko.utils.unwrapObservable(value) ? $(element).fadeIn() : $(element).fadeOut();
      }
  };
   
  ko.applyBindings(new PlanetsModel());

  </script>


	@if (Auth::user()->confirmed)
	<script>

    $(function() {   
    	 $( "#nit" ).prop( "disabled", true );
    	 $( "#name" ).prop( "disabled", true );
    	 $( "#unipersonal" ).prop( "disabled", true );
    	 $( "#uniper" ).prop( "disabled", true );

    	 
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