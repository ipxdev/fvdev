@extends('accounts.nav')

@section('content')


@if (Session::has('message'))
    <div class="alert alert-danger"></div>
@endif

<br>
  {{ Former::legend($title) }}

    {{ Former::open_for_files($url)->method($method)->addClass('col-md-8 col-md-offset-2 warn-on-exit')->rules(array( 
        'name' => 'required|match:/[a-zA-Z. ]+/|min:3',
        'description' => 'match:/[a-zA-Z. ]+/|min:3'
    )); }}

    @if ($category)

      {{ Former::populate($category) }}
      
    @endif


    {{ Former::legend('category') }}

    {{ Former::text('name')->label('Nombre (*)') }}
    {{ Former::text('description')->label('Descripción (*)') }}

<hr>
      {{ Former::actions( 
          Button::lg_default_link('company/categories', 'Cancelar')->append_with_icon('remove-circle'),
          Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk')
      ) }}


  {{ Former::close() }}

    <script type="text/javascript">

  </script>

@stop