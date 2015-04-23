@extends('accounts.nav')

@section('content') 
  @parent

  {{ Former::open($url)->method($method)->addClass('col-md-12 warn-on-exit')->rules(array( 
      'name' => 'required'
  )); }}


  {{ Former::legend($title) }}

  @if ($manual)
    {{ Former::populate($manual) }}
  @endif
  <div class="row">
    <div class="col-md-6">  

     {{ Former::legend('Factura') }}
    {{ Former::text('name')->label('texts.invoice_number') }}


    </div>

    <div class="col-md-6">    

    
    </div>
  </div>


  {{ Former::actions( 
      Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk'),
      Button::lg_default_link('company/manuals', 'Cancelar')->append_with_icon('remove-circle')      
  ) }}

  {{ Former::close() }}

    <script type="text/javascript">

    $(function() {
      $('#country_id').combobox();
    });

  </script>

@stop