@extends('master')

@section('head')	

  <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/> 
  <link href="{{ asset('css/style.css') }}" rel="stylesheet" type="text/css"/>    

  <style type="text/css">
  		body {
  		  padding-top: 40px;
  		  padding-bottom: 40px;
  		}
      .modal-header {
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
       }
      .modal-header h4 {
        margin:0;
      }
      .modal-header img {

      }
      .form-signin {
    	  max-width: 400px;
    	  margin: 50px  auto !important;
        background: #fff;
      }
      p.link a {
        font-size: 11px;
      }
      .form-signin .inner {
  	    padding: 20px;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
        border-left: 1px solid #ddd;
        border-right: 1px solid #ddd;
        border-bottom: 1px solid #ddd;
		  }
  		.form-signin .checkbox {
  		  font-weight: normal;
  		}
  		.form-signin .form-control {
  		  margin-bottom: 17px !important;
  		}
  		.form-signin .form-control:focus {
  		  z-index: 2;
  		}
      .titlefv {
        float: right;
      }
		
  </style>

@stop

@section('body')
    <div class="container">


{{ Form::open(array('url' => 'get_started', 'id' => 'startForm')) }}
{{ Form::hidden('guest_key') }}
{{ Form::close() }}


		{{ Former::open('login')->addClass('form-signin') }}
			<div class="modal-header">
          <img style="display:block;margin:0 auto 0 auto;" src="{{ asset('images/icon-login.png') }}" />      
      </div>
      
      <div class="inner">

      <h3 style="text-align: center; margin:0 auto 0 auto; color:#404040;">Crear Cuenta</h3>
      
      <hr>
			
      <p>
                  {{ Former::text('code')->label('')->placeholder('Ingrese Código') }}
			</p>

      <hr>

			<p><center>
      <button type="button" class="btn btn-primary" id="proPlanButton" onclick="submitPlan()">Aceptar</button>                    
</center>
      </p>


		{{ Former::close() }}

      </div>
    </div>



<script type="text/javascript">

    function submitPlan() {

      $.ajax({
        type: 'POST',
        url: '{{ URL::to('get_started') }}',
        data: 'code=' + encodeURIComponent($('form.form-signin #code').val()) + 
      '&go_pro=' + $('#go_pro').val(),
        success: function(result) { 
          if (result == 'success')
          { 

            window.location = '{{ URL::to('company/details') }}';
          }

        }
      });     

  }
</script>  

@stop