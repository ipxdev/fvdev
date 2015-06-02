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
        'name' => 'required',
        'aux2' => 'required',
        'address1' => 'required',
        'postal_code' => 'required',
        'address2' => 'required',
        'city' => 'required',
        'activity_pri' => 'required',
        'law' => 'required',
        'state' => 'required',
        'aux2' => 'required',
        'deadline' => 'required|after:2015-05-10'
    )); }}
    {{ Former::populate($branch) }}
  @else
      {{ Former::open_for_files($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array( 
        'name' => 'required',
        'aux2' => 'required',
        'address1' => 'required',
        'postal_code' => 'required',
        'address2' => 'required',
        'city' => 'required',
        'activity_pri' => 'required',
        'law' => 'required',
        'state' => 'required',
        'aux2' => 'required',
        'deadline' => 'required|after:2015-05-11',
        'dosage' => 'required',

    )); }}
  @endif
  <div class="row">
    <div class="col-md-6">  

    {{ Former::legend('branch') }}

    {{ Former::text('name')->label('Nombre (*)') }}

    {{ Former::radios('aux2')->label('tipo (*)')
         ->radios(array(
           'Productos' => array('name' => 'aux2', 'value' => '1'),
           'Servicios' => array('name' => 'aux2', 'value' => '2'),
    )) }}

    {{ Former::legend('address') }} 
    {{ Former::textarea('address2')->label('Dirección (*)') }}
    {{ Former::textarea('address1')->label('Zona/Barrio (*)') }}
    {{ Former::text('postal_code')->label('teléfono (*)') }}
    {{ Former::text('city')->label('departamento (*)') }}
    {{ Former::text('state')->label('municipio (*)') }}

    {{-- Former::select('country_id')->addOption('','')->label('Departamento')
          ->fromQuery($countries, 'name', 'id') --}}
    </div>

    <div class="col-md-6">    

      {{ Former::legend('Actividad Económica') }}

      {{ Former::textarea('activity_pri')->label('actividad Principal  (*)') }}
      {{ Former::textarea('activity_sec1')->label('actividad Secundaria')->rows(1) }}


      {{ Former::legend('dosificación') }}

      {{ Former::file('dosage')->label(' ')->inlineHelp(trans('texts.dosage_help')) }}

      @if ($branch)

        {{ Former::uneditable('aux1')->label('núm. de Trámite ')->class('uneditable') }}

        {{ Former::uneditable('number_autho')->label('núm. de autorización ')->class('uneditable') }}

        {{ Former::uneditable('key_dosage')->label('llave de Dosidicación ')->class('uneditable')->rows(4) }}
      
      @endif

      <br>

      {{ Former::date('deadline')->label('Fecha Límite') }}   
                 

      {{ Former::legend('Leyendas') }}

      {{ Former::textarea('law')->label('leyenda Genérica  (*)') }}

    
    </div>
  </div>

  @if ($aux == 'no')

      {{ Former::actions( 
          Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
          Button::lg_default_link('company/branches', 'Cancelar')->append_with_icon('remove-circle')      
      ) }}

  @else

        @if (!$branch->isValid1())

            @if (Utils::isPro())
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
                  Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
                  Button::lg_default_link('company/branches', 'Cancelar')->append_with_icon('remove-circle')      
                  ) }}
            @endif

        @else

          {{ Former::actions( 
          Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
          Button::lg_default_link('company/branches', 'Cancelar')->append_with_icon('remove-circle')      
          ) }}
       
        @endif

  @endif

  {{ Former::close() }}

    <script type="text/javascript">

    $(function() {
      $('#country_id').combobox();
    });

    document.oncontextmenu = function(){return false}

  </script>

@stop