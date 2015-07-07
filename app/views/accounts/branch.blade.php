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
    {{ Former::populate($branch) }}
    {{ Former::populateField('branch_name', $branch->name()) }}
  @endif

      {{ Former::open_for_files($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array( 
        'branch_name' => 'required',
        'branch_type_id' => 'required',
        'address1' => 'required',
        'work_phone' => 'required|Numeric|match:/[0-9.-]+/|min:7',
        'address2' => 'required',
        'city' => 'required',
        'economic_activity' => 'required',
        'state' => 'required',
        'deadline' => 'required|after:2015-12-23',
        'number_process' => 'required|match:/[0-9]+/|min:8',
        'number_autho' => 'required|match:/[0-9]+/|min:10',  
        'key_dosage' => 'required'
    )); }}
 
  <div class="row">
    <div class="col-md-6">  

    {{ Former::legend('branch') }}

    {{ Former::text('branch_name')->label('Nombre (*)')->title('Ejem. Casa Matriz o Sucursal 1') }}

    {{ Former::select('branch_type_id')->addOption('','')->label('tipo  (*)')
        ->fromQuery($branch_types, 'name', 'id') }}

    {{ Former::textarea('economic_activity')->label('Actividad Económica  (*)') }}

    {{ Former::legend('address') }} 
    {{ Former::text('address2')->label('Dirección (*)') }}
    {{ Former::text('address1')->label('Zona/Barrio (*)') }}
    {{ Former::text('work_phone')->label('teléfono (*)') }}
    {{ Former::text('city')->label('ciudad (*)') }}
    {{ Former::text('state')->label('municipio (*)') }}
          
    </div>

    <div class="col-md-6">    

      {{ Former::legend('dosificación') }}

      {{ Former::text('number_process')->label('núm. de Trámite (*)') }}

      {{ Former::text('number_autho')->label('núm. de Autorización (*)') }}

      {{ Former::date('deadline')->label('Fecha Límite Emisión (*)') }} 

      {{ Former::textarea('key_dosage')->label('Archivo con la Llave (*)') }}
      
      {{-- Former::file('dosage')->label('Archivo con la Llave (*)')->inlineHelp(trans('texts.dosage_help')) --}}

      @if ($branch)

        {{-- Former::uneditable('key_dosage')->label('llave de Dosificación ')->class('uneditable')->rows(4) --}}
      
      @endif
      
      <hr>
      {{ Former::legend('información Adicional') }}

      {{ Former::checkbox('third_view')->label('Facturación por Terceros')->text('Seleccione si fuera el caso')->data_bind("checked: displayAdvancedOptions") }}

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

    var Model = function() {
        
        this.displayAdvancedOptions = ko.observable({{ $third ? 'true' : 'false' }});
    };
     
    ko.applyBindings(new Model());

    document.oncontextmenu = function(){return false}

    </script>

@stop