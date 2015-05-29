@extends('accounts.nav')

@section('content')


@if (Session::has('message'))
    <div class="alert alert-danger"></div>
@endif


  {{ Former::legend($title) }}

    {{ Former::open_for_files($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array( 
        'name' => 'required'
    )); }}

    @if ($category)

      {{ Former::populate($category) }}
      
    @endif

  <div class="row">
    <div class="col-md-6">  

    {{ Former::legend('category') }}

    {{ Former::text('name')->label('Nombre (*)') }}

    </div>

    <div class="col-md-6">    
    
    </div>
  </div>


      {{ Former::actions( 
          Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
          Button::lg_default_link('company/categories', 'Cancelar')->append_with_icon('remove-circle')      
      ) }}


  {{ Former::close() }}

    <script type="text/javascript">

  </script>

@stop