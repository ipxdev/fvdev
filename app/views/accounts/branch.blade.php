@extends('accounts.nav')

@section('content') 

@if (Session::has('message'))
    <div class="alert alert-danger"></div>
@endif

  {{ Former::open($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array( 
      'name' => 'required',
      'address1' => 'required',
      'postal_code' => 'required',
      'address2' => 'required',
      'city' => 'required',
      'activity_pri' => 'required',
      'number_autho' => 'required',
      'deadline' => 'required',
      'key_dosage' => 'required',
      'law' => 'required',
      'state' => 'required'
  )); }}


  {{ Former::legend($title) }}

  @if ($branch)
    {{ Former::populate($branch) }}
  @endif
  <div class="row">
    <div class="col-md-6">  

    {{ Former::text('name')->label('Nombre (*)') }}

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
      {{ Former::textarea('activity_sec1')->label('actividad Secundaria') }}

      {{ Former::legend('dosificación') }}

      {{ Former::text('number_autho')->label('núm. de autorización  (*)') }}
                        <div class="row">

                          <div class="col-md-3" style="margin-left:64px;">                      
                           {{Former::label('fecha límite  (*)')}} 
                          </div>
                          <div class="col-md-3" style="margin-left:-15px;">                      
                          {{ Former::text('day')->label('')->pattern('[0-9]{2}')->maxlength('2')->placeholder('día') }} 
                          </div>
                          <div class="col-md-3" style="margin-left:-60px;">
                          {{ Former::text('month')->label('/')->pattern('[0-9]{2}')->maxlength('2')->placeholder('mes') }}   
                          </div>
                          <div class="col-md-4" style="margin-left:-38px;">
                          {{ Former::text('year')->label('/')->pattern('[0-9]{4}')->length('4')->placeholder('año') }}   
                          </div>
                        </div>
      {{ Former::textarea('key_dosage')->label('llave dosificación  (*)')->rows(3)}}

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


  @endif

  {{ Former::close() }}

    <script type="text/javascript">

    $(function() {
      $('#country_id').combobox();
    });

  </script>

@stop