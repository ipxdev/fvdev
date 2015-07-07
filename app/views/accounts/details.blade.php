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
  		'name' => 'required|min:3',
  		'email' => 'email|required',
  		'nit' => 'required|Numeric|min:5',
  		'uniper' => 'min:4',
  		'work_phone' => 'required|match:/[0-9.-]+/|min:7',
  		'address1' => 'required|min:4',
  		'address2' => 'required|min:4',
  		'first_name' => 'required|match:/[a-zA-Z. ]+/|min:3',
  		'last_name' => 'required|match:/[a-zA-Z. ]+/|min:3',
  		'phone' => 'required|Numeric|match:/[0-9.-]+/|min:8'
  		
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
			{{ Former::text('nit')->label('NIT (*)')->title('Solo se acepta Números') }}
			{{ Former::text('name')->label('EMPRESA (*)') }}


			{{ Former::checkbox('unipersonal')->label(' ')->text('unipersonal')->data_bind("checked: displayAdvancedOptions") }}

			{{ Former::legend('nombre de Contribuyente')->data_bind("fadeVisible: displayAdvancedOptions") }}
		    {{ Former::text('uniper')->label(' ')->data_bind("Visible:none,fadeVisible: displayAdvancedOptions") }}    


			{{ Former::legend('address')  }}
			{{ Former::text('address2')->label('Dirección (*)') }}
			{{ Former::text('address1')->label('Zona/Barrio (*)') }}
			{{ Former::text('work_phone')->label('Teléfono (*)')->title('Solo se acepta Número Telefónico') }}			

		</div>
	
		<div class="col-md-6">		

			@if ($showUser)
				{{ Former::legend('Administrador') }}
				{{ Former::text('first_name')->label('Nombre(s) (*)')->title('Solo se acepta Letras') }}
				{{ Former::text('last_name')->label('Apellidos (*)')->title('Solo se acepta Letras') }}
				{{ Former::text('phone')->label('Celular (*)')->title('Solo se acepta Número Telefónico') }}
				{{ Former::text('email')->label('Correo electrónico (*)') }}

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

  var Model = function() {
      
      this.displayAdvancedOptions = ko.observable({{ $account->is_uniper ? 'true' : 'false' }});
      this.showPlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }
      this.hidePlanetElement = function(elem) { if (elem.nodeType === 1) $(elem).slideUp(function() { $(elem).remove(); }) }
  };
   
  ko.bindingHandlers.fadeVisible = {
      init: function(element, valueAccessor) {
          var value = valueAccessor();
          $(element).toggle(ko.utils.unwrapObservable(value));
      },
      update: function(element, valueAccessor) {
          var value = valueAccessor();
          ko.utils.unwrapObservable(value) ? $(element).fadeIn() : $(element).fadeOut();
      }
  };
   
  ko.applyBindings(new Model());

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

@stop