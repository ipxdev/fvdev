@extends('accounts.nav')

@section('content') 


  {{ Former::open($url)->method($method)->addClass('col-md-10 col-md-offset-1 warn-on-exit')->rules(array(
      'first_name' => 'required',
      'last_name' => 'required',
      'email' => 'required|email',
      'username' => 'required',
      'phone' => 'required',
  )); }}

  {{ Former::legend($title) }}

  @if ($user)
    {{ Former::populate($user) }} 
    {{ Former::populateField('username', $user->new_username) }}   
    {{ Former::populateField('branch_id', $user->branch_id) }}
  @endif
  <div class="row">
      <div class="col-md-6">
      {{ Former::text('first_name')->label('Nombre(s) (*)') }}
      {{ Former::text('last_name')->label('Apellidos (*)') }}
      {{ Former::text('email')->label('Email (*)') }}

      {{ Former::text('phone')->label('Teléfono/Celular (*)') }}

       </div>
    <div class="col-md-6">

      {{ Former::legend('Nombre de Usuario') }}

      {{ Former::text('username')->label('usuario (*)') }}

      {{ Former::legend('Tipo de Usuario') }}

      {{ Former::checkbox('facturador')->label(' ')->text('facturador')->data_bind("checked: displayAdvancedOptions") }}

      {{ Former::legend('Sucursal')->data_bind("fadeVisible: displayAdvancedOptions") }}    
      {{ Former::select('branch_id')->label(' ')
      ->data_bind("fadeVisible: displayAdvancedOptions")->fromQuery($branches, 'name', 'id') }}

    </div>
  </div>

  <script type="text/javascript">



  var PlanetsModel = function() {
      
      this.displayAdvancedOptions = ko.observable({{ $b ? 'true' : 'false' }});
   
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

  {{ Former::actions( 
      Button::lg_default_link('company/user_management', 'Cancel')->append_with_icon('remove-circle'),     
      Button::lg_success_submit(trans($user && $user->confirmed ? 'texts.save' : 'texts.send_invite'))->append_with_icon($user && $user->confirmed ? 'floppy-disk' : 'send')
  ) }}

  {{ Former::close() }}

@stop