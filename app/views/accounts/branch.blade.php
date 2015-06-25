@extends('accounts.nav_advanced')

@section('content')


@if (Session::has('message'))
    <div class="alert alert-danger"></div>
@endif


    <script type="text/javascript">

    function disabletext(e){
    return false
    }
    function reEnable(){
    return true
    }
    document.onselectstart=new Function ("return false")
    if (window.sidebar){
    document.onmousedown=disabletext
    document.onclick=reEnable
    }

   </script>

  {{ Former::legend($title) }}
  @parent



  @if ($branch)

    {{ Former::open_for_files($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array( 

        'branch_name' => 'required|match:/[a-zA-Z0-9. º° ]+/|min:8',
        'aux2' => 'required',
        'address1' => 'required|min:4',
        'postal_code' => 'required|Numeric|match:/[0-9.-]+/|min:7',
        'address2' => 'required|min:4',
        'city' => 'required|min:4',
        'activity_pri' => 'required|match:/[a-zA-Z]+/|min:4',
        'activity_sec1' => 'match:/[a-z A-Z]+/|min:4',
        'state' => 'required|min:4',
        'deadline' => 'required|after:2015-12-23',
        'aux1' => 'required|match:/[0-9]+/|min:8',
        'number_autho' => 'required|match:/[0-9]+/|min:10'      
    )); }}
    {{ Former::populate($branch) }}
    {{ Former::populateField('branch_name', $branch->name()) }}
  @else
      {{ Former::open_for_files($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array( 

        'branch_name' => 'required|match:/[a-z A-Z0-9. º°]+/|min:8',
        'aux2' => 'required',
        'address1' => 'required|min:4',
        'postal_code' => 'required|Numeric|match:/[0-9.-]+/|min:7',
        'address2' => 'required|min:4',
        'city' => 'required|min:4',
        'activity_pri' => 'required|match:/[a-zA-Z]+/|min:4',
        'activity_sec1' => 'match:/[a-z A-Z]+/|min:4',
        'state' => 'required|min:4',
        'deadline' => 'required|after:2015-12-23',
        'aux1' => 'required|match:/[0-9]+/|min:8',
        'number_autho' => 'required|match:/[0-9]+/|min:10',  
        'dosage' => 'required'
    )); }}
  @endif
  <div class="row">
    <div class="col-md-6">  

    {{ Former::legend('branch') }}

    {{ Former::text('branch_name')->label('Nombre (*)')->title('Ejem. Casa Matriz o Sucursal 1') }}

    {{ Former::textarea('activity_pri')->label('Actividad Económica  (*)') }}

    {{ Former::radios('aux2')->label('tipo (*)')
         ->radios(array(
           'Productos' => array('name' => 'aux2', 'value' => '1'),
           'Servicios' => array('name' => 'aux2', 'value' => '2'),
    )) }}

    {{ Former::legend('address') }} 
    {{ Former::text('address2')->label('Dirección (*)') }}
    {{ Former::text('address1')->label('Zona/Barrio (*)') }}
    {{ Former::text('postal_code')->label('teléfono (*)') }}
    {{ Former::text('city')->label('ciudad (*)') }}
    {{ Former::text('state')->label('municipio (*)') }}

    {{-- Former::select('country_id')->addOption('','')->label('Departamento')
          ->fromQuery($countries, 'name', 'id') --}}
          
    </div>

    <div class="col-md-6">    


      {{ Former::legend('dosificación') }}

      {{ Former::text('aux1')->label('núm. de Trámite (*)') }}

      {{ Former::text('number_autho')->label('núm. de Autorización (*)') }}

      {{ Former::date('deadline')->label('Fecha Límite Emisión (*)') }} 
      

      {{ Former::file('dosage')->label('Archivo con la Llave (*)')->inlineHelp(trans('texts.dosage_help')) }}
      


      @if ($branch)

        {{ Former::uneditable('key_dosage')->label('llave de Dosificación ')->class('uneditable')->rows(4) }}
      
      @endif
      
      <hr>
      {{ Former::legend('información Adicional') }}

      {{ Former::checkbox('third_view')->label('Facturación por Terceros')->text('Seleccione si fuera el caso')->data_bind("checked: displayAdvancedOptions") }}

      {{-- Former::textarea('activity_sec1')->label('actividad Secundaria')->rows(1) --}}

      {{-- Former::legend('Leyendas') --}}

      {{-- Former::textarea('law')->label('leyenda Genérica  (*)') --}}
    
    </div>
  </div>
<br>
  @if ($aux == 'no')

      {{ Former::actions( 
          Button::lg_default_link('company/branches', 'Cancelar')->append_with_icon('remove-circle'),
          Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk')
      
      ) }}

  @else

        @if (!$branch->isValid1())

            @if (Utils::isConfirmed())
                  {{ Former::actions( 
                    Button::lg_default_link('company/branches', 'Volver')      
                  ) }}
                <script>
                  $(function() {   
                   $('form.warn-on-exit input').prop('disabled', true);
                   $('form.warn-on-exit textarea').prop('disabled', true);
                  });
                </script> 
            @else
                  {{ Former::actions( 
                  Button::lg_default_link('company/branches', 'Cancelar')->append_with_icon('remove-circle'),      
                  Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk')
                  ) }}
            @endif

        @else

          {{ Former::actions( 
          Button::lg_default_link('company/branches', 'Cancelar')->append_with_icon('remove-circle'), 
          Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk')
          ) }}
       
        @endif

  @endif

  {{ Former::close() }}

    <script type="text/javascript">

    $(function() {
      $('#country_id').combobox();
    });

    var PlanetsModel = function() {
        
        this.displayAdvancedOptions = ko.observable({{ $branch->third ? 'true' : 'false' }});
    };
     
    ko.applyBindings(new PlanetsModel());

    document.oncontextmenu = function(){return false}

    </script>

@stop