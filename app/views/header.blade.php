@extends('master')

@section('head')

<link href="{{ asset('built.css') }}" rel="stylesheet" type="text/css"/>    

<style type="text/css">

  body {
    background-color: #EEEEEE;
    padding-top: 114px; 
  }

  /* Fix for header covering stuff when the screen is narrower */
  @media screen and (min-width: 1200px) {
    body {
      padding-top: 56px; 
    }
  }
</style>

@include('script')

<script type="text/javascript">

  /* Set the defaults for DataTables initialisation */
  $.extend( true, $.fn.dataTable.defaults, {
    "sDom": "t<'row-fluid'<'span6'i><'span6'p>>",
    "sPaginationType": "bootstrap",
    "bInfo": true,
    "oLanguage": {
      'sEmptyTable': "{{ trans('texts.empty_table') }}",
      'sLengthMenu': '_MENU_',
      'sSearch': ''
    }
  } );

</script>
@stop

@section('body')


<nav class="navbar navbar-default navbar-fixed-top" role="navigation">

  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse-1">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a href="{{ URL::to('/') }}" class='navbar-brand'>
        <img src="{{ asset('images/logo-factura-virtual.png') }}" style="height:25px;margin-top:-5px;width:auto"/>
      </a>      
    </div>
      
    @if (Auth::user()->confirmed)

      <div style="font-size:15px; margin:0 ;color:#fff;text-align:right;">

        {{ Auth::user()->getDisplayName() }} |

        @if (Utils::isAdmin())
        <a href="{{ URL::to('/select_branch') }}" style="color:#00B0DC!important;">
        
        {{ Auth::user()->getDisplayBranch() }}
        <span style="margin:3px 0" class="glyphicon glyphicon-chevron-down"></span>
        </a>
        @else
        {{ Auth::user()->getDisplayBranch() }}
        <span style="margin:3px 0" class="glyphicon glyphicon-chevron-down"></span>
        @endif

      </div>

    @endif

   </div>

  <div class="container">



    <div class="collapse navbar-collapse" id="navbar-collapse-1">
    @if (Auth::user()->confirmed)
      <ul class="nav navbar-nav">

        {{ HTML::nav_link('dashboard', 'dashboard') }}

        {{ HTML::menu_link('client') }}
        {{ HTML::menu_link('invoice') }}
        {{ HTML::menu_link('payment') }}  

        {{ HTML::menu_link('credit') }}
        {{ HTML::menu_linkProduct('product') }}

        @if (Utils::isPro())
          {{-- HTML::menu_link('quote') --}}
        @endif
      </ul>
    @endif
      <div class="navbar-form navbar-right">

        @if (Auth::check())
          @if (!Auth::user()->confirmed)

            {{-- Button::sm_success_primary(trans('texts.sign_up'), array('id' => 'signUpButton', 'data-toggle'=>'modal', 'data-target'=>'#signUpModal')) --}} &nbsp;
            {{ Button::sm_primary('Calcular Código de Control', array('class' => 'btncc', 'id' => 'proPlanButton2', 'data-toggle'=>'modal', 'data-target'=>'#proPlanModal2')) }} &nbsp;
            {{ Button::sm_success_primary('COMENZAR LA CONFIGURACIÓN', array('id' => 'proPlanButton', 'data-toggle'=>'modal', 'data-target'=>'#PlanModal')) }} &nbsp;

          @else

          <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="modal" data-target="#proPlanModal">

          {{ Auth::user()->account->getCreditCounter() }}

          </button> 

          @endif
        @endif


    @if (Auth::user()->confirmed)

        <div class="btn-group">
          <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
            <span id="myAccountButton">
            </span>
            <span class="glyphicon glyphicon-cog"></span>
          </button>     
          <ul class="dropdown-menu fvlink" role="menu">
            @if (Utils::isAdmin())

              <li style="font-size:14px;">{{ link_to('company/user_management', uctrans('Gestión de Usuarios')) }}</li>
              <li class="divider"></li>

              <li style="font-size:14px;"><a href="{{ url('company/details') }}">{{ uctrans('texts.advanced_settings') }}</a></li>
              <li class="divider"></li>

            @endif



            <li style="font-size:14px;">{{ link_to('company/import_export', uctrans('texts.import_export')) }}</li>
            <li class="divider"></li>
            
            <li><a href="{{ url('company/chart_builder') }}">{{ uctrans('Gráficas/Reportes') }}</a></li>
            <li class="divider"></li>

            <li class="fvlinkred" style="font-size:14px;">{{ link_to('#', trans('texts.logout'), array('onclick'=>'logout()')) }}</li>
          </ul>
        </div>


        @if (Auth::user()->getPopOverText() && Utils::isRegistered())
        <button id="ninjaPopOver" type="button" class="btn btn-default" data-toggle="popover" data-placement="bottom" data-content="{{ Auth::user()->getPopOverText() }}" data-html="true" style="display:none">
          {{ Auth::user()->getDisplayName() }}
        </button>
        @endif

@endif

      </div>  

@if (Utils::isPro())
      <form class="navbar-form navbar-right" role="search">
        <div class="form-group">
          <input type="text" id="search" class="form-control" placeholder="{{ trans('texts.search') }}">
        </div>
      </form>

      <ul class="nav navbar-nav navbar-right">        
        <li class="dropdown">
          <a style="line-height: 5px!important" href="#" class="dropdown-toggle" data-toggle="dropdown">{{ trans('texts.history') }} <b class="caret"></b></a>
          <ul class="dropdown-menu">                        
            @if (count(Session::get(RECENTLY_VIEWED)) == 0)
            <li><a href="#">{{ trans('texts.no_items') }}</a></li>
            @else
            @foreach (Session::get(RECENTLY_VIEWED) as $link)
            <?php
                $mystring = $link->name;
                $findme = 'Invoice';
                $new_link = '';
                $pos = strpos($mystring, $findme);
                    if ($pos !== false)
                    {
                      $new_link = substr($link->name, 7);
                      $new_link = 'Factura'.$new_link;

                    }else 
                    {
                      $findme = 'Client';
                      $new_link = '';
                      $pos = strpos($mystring, $findme);
                          if ($pos !== false) 
                          {
                            $new_link = substr($link->name, 6);
                            $new_link = 'Cliente'.$new_link;
                          }
                          else 
                          {
                            $findme = 'Quote';
                            $new_link = '';
                            $pos = strpos($mystring, $findme);
                                if ($pos !== false) 
                                {
                                  $new_link = substr($link->name, 6);
                                  $new_link = 'Recibo'.$new_link;
                                }
                                else
                                {
                                    $link->name = $new_link;
                                }
                          }
                    }
                ?>
            <li><a href="{{ $link->url }}">{{ $new_link }}</a></li> 
            @endforeach
            @endif
          </ul>
        </li>
      </ul>
@endif
      
    </div><!-- /.navbar-collapse -->


  </div>
</nav>



<br/>
<div class="container">   

  @if (!isset($showBreadcrumbs) || $showBreadcrumbs)
  {{ HTML::breadcrumbs() }}
  @endif

  @if (Session::has('warning'))
  <div class="alert alert-warning">{{ Session::get('warning') }}</div>
  @endif

  @if (Session::has('message'))
    <div class="alert alert-info">
      {{ Session::get('message') }}
    </div>
  @elseif (Session::has('news_feed_message'))
    <div class="alert alert-info">
      {{ Session::get('news_feed_message') }}      
      <a href="#" onclick="hideMessage()" class="pull-right">{{ trans('texts.hide') }}</a>      
    </div>
  @endif

  @if (Session::has('error'))
  <div class="alert alert-danger">{{ Session::get('error') }}</div>
  @endif

  @yield('content')   

</div>
<div class="container">
  <div class="footer" style="padding-top: 32px">
    @if (false)
    <div class="pull-right">
      {{ Former::open('user/setTheme')->addClass('themeForm') }}
      <div style="display:none">
        {{ Former::text('theme_id') }}
        {{ Former::text('path')->value(Request::url()) }}
      </div>
      <div class="btn-group tr-action dropup">
        <button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown">
          Site Theme <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
          <li><a href="#" onclick="setTheme(0)">Default</a></li>
          @foreach (Theme::remember(DEFAULT_QUERY_CACHE)->get() as $theme)
          <li><a href="#" onclick="setTheme({{ $theme->id }})">{{ ucwords($theme->name) }}</a></li>
          @endforeach
        </ul>
      </div>
      {{ Former::close() }}         
    </div>
    @endif

</div>      
</div>
</div>



<div class="modal fade" id="signUpModal" tabindex="-1" role="dialog" aria-labelledby="signUpModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="padding-bottom:10px!important;background-color:#016797!important;">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">{{ trans('texts.sign_up') }}</h4>
      </div>

      <div style="background-color: #fff; padding-right:10px; padding-left:10px;" id="signUpDiv" onkeyup="validateSignUp()" onclick="validateSignUp()" onkeydown="checkForEnter(event)">
        <br/>

        {{ Former::open('signup/submit')->addClass('signUpForm') }}

        @if (Auth::check())
        {{ Former::populateField('new_nit', Auth::user()->account->getNit()); }}
        {{ Former::populateField('new_name', Auth::user()->account->getName()); }}
        {{ Former::populateField('new_first_name', Auth::user()->first_name); }}
        {{ Former::populateField('new_last_name', Auth::user()->last_name); }}
        {{ Former::populateField('new_email', Auth::user()->email); }}

        @endif

        <div style="display:none">
          {{ Former::text('path')->value(Request::path()) }}
          {{ Former::text('go_pro') }}
        </div>
        {{ Former::legend('Empresa') }}
        {{ Former::text('new_nit')->label('NIT') }}
        {{ Former::text('new_name')->label('Nombre de Empresa') }}
        
        {{ Former::legend('Administrador') }}
        <div class="row">
          <div class="col-md-6">
        {{ Former::text('new_first_name')->label(trans('texts.first_name')) }}
          </div>
          <div class="col-md-6">
        {{ Former::text('new_last_name')->label(trans('texts.last_name')) }}
          </div>
        </div>
        {{ Former::text('new_email')->label(trans('texts.email')) }}   
        {{ Former::text('new_username')->label('nombre de Usuario') }}      
        {{ Former::password('new_password')->label(trans('texts.password'))->placeholder('mínimo seis caracteres') }}        
        {{ Former::close() }}

        <center><div id="errorTaken" style="display:none">&nbsp;<br/>{{ trans('texts.email_taken') }}</div></center>
        <br/>


      </div>

      <div style="padding-left:40px;padding-right:40px;display:none;min-height:130px" id="working">
        <h3>{{ trans('texts.working') }}...</h3>
        <div class="progress progress-striped active">
          <div class="progress-bar"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
        </div>
      </div>

      <div style="background-color: #fff; padding-right:20px;padding-left:20px; display:none" id="signUpSuccessDiv">
        <br/>
        <h3>{{ trans('texts.success') }}</h3>
        {{ trans('texts.success_message') }}<br/>&nbsp;
      </div>


      <div class="modal-footer" id="signUpFooter" style="margin-top: 0px">          
        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="validateServerSignUp2()">{{ trans('texts.close') }} <i class="glyphicon glyphicon-remove-circle"></i></button>
        <button type="button" class="btn btn-primary" id="saveSignUpButton" onclick="validateServerSignUp()" disabled>{{ trans('texts.save') }} <i class="glyphicon glyphicon-floppy-disk"></i></button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"style="padding-bottom:10px!important;background-color:#016797!important;">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">{{ trans('texts.logout') }}</h4>
      </div>

      <div class="container">      
        <h3>{{ trans('texts.are_you_sure') }}</h3>
        <p>{{ trans('texts.erase_data') }}</p>          
      </div>

      <div class="modal-footer" id="signUpFooter">          
        <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('texts.cancel') }}</button>
        <button type="button" class="btn btn-primary" onclick="logout(true)">{{ trans('texts.logout') }}</button>         
      </div>
    </div>
  </div>
</div>



  <div class="modal fade" id="proPlanModal" tabindex="-1" role="dialog" aria-labelledby="proPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog medium-dialog">
      <div class="modal-content">
        <div class="modal-header"style="padding-bottom:10px!important;background-color:#016797!important;">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="proPlanModalLabel">RECARGAR FACTURAS</h4>
        </div>

        <div style="background-color: #fff; padding-left: 16px; padding-right: 16px" id="proPlanDiv">

            <div class="row">
              <div class="col-md-12">
                <HR>
                <p>
                Cuenta con {{ Auth::user()->account->getCreditCounter() }} Facturas Disponibles</p>
                <br>
                <ul class="list-group">


                </ul>

              </div>
            </div>              

      </div>


      <div style="padding-left:40px;padding-right:40px;display:none;min-height:130px" id="proPlanWorking">
        <h3>{{ trans('texts.working') }}...</h3>
        <div class="progress progress-striped active">
          <div class="progress-bar"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
        </div>
      </div>

      <div style="background-color: #fff; padding-right:20px;padding-left:20px; display:none" id="proPlanSuccess">
        &nbsp;<br/>
        {{ trans('texts.pro_plan_success') }}
        <br/>&nbsp;
      </div>

       <div class="modal-footer" style="margin-top: 0px" id="proPlanFooter">
          <button type="button" class="btn btn-default" id="proPlanButton" data-dismiss="modal">CERRAR</button>          
          <button type="button" class="btn btn-primary" id="proPlanButton" onclick="submitProPlan()">ACEPTAR</button>                    
       </div>     
      </div>
    </div>
  </div>




@if (Auth::check() && !Auth::user()->isPro())
  <div class="modal fade" id="PlanModal" tabindex="-1" role="dialog" aria-labelledby="PlanModalLabel" aria-hidden="true">
    <div class="modal-dialog medium-dialog">
      <div class="modal-content">
        <div class="modal-header"style="padding-bottom:10px!important;background-color:#016797!important;">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="PlanModalLabel">COMENZAR LA CONFIGURACIÓN</h4>
        </div>

        <div style="background-color: #fff; padding-left: 16px; padding-right: 16px" id="proPlanDiv">

            <div class="row">
              <div class="col-md-12">
                <HR>
                <p>Para configurar tu cuenta, requieres los datos del Padrón Biométrico Digital del NIT, tener habilitada la modalidad de Facturación Computarizada, obtener la  llave de dosificación y el Logo de tu empresa.</p>
                <br>
                <ul class="list-group">

                @if(Auth::user()->account->getOp1())        
                  <a href="{{ URL::to('company/details') }}" style="color:#333333;text-decoration:none;"><li class="list-group-item ipxhover2"><b>1. Perfil de la Empresa</b><br> <i>Registra el NIT y nombre de tu empresa. Establece los datos del administrador y otros ajustes que no podrán ser modificados.</i></li></a>
                @else
                  <a href="{{ URL::to('company/details') }}" style="color:#333333;text-decoration:none;"><li class="list-group-item ipxhover1"><b>1. Perfil de la Empresa</b><br> <i>Registra el NIT y nombre de tu empresa. Establece los datos del administrador y otros ajustes que no podrán ser modificados.</i></li></a>               
                @endif

                @if(Auth::user()->account->getOp2()) 
                  <a href="{{ URL::to('company/branches') }}" style="color:#333333;text-decoration:none;"><li class="list-group-item ipxhover2"><b>2. Datos de Sucursal</b><br> <i>Impuestos Nacionales proporciona el archivo con las llaves de dosificación para activar la facturación computarizada por sucursal. Los datos adicionales deben ser exactamente los que fueron registrados en el PBD.</i></li></a>
                @else
                  <a href="{{ URL::to('company/branches') }}" style="color:#333333;text-decoration:none;"><li class="list-group-item ipxhover1"><b>2. Datos de Sucursal</b><br> <i>Impuestos Nacionales proporciona el archivo con las llaves de dosificación para activar la facturación computarizada por sucursal. Los datos adicionales deben ser exactamente los que fueron registrados en el PBD.</i></li></a>                
                @endif

                @if(Auth::user()->account->getOp3())  
                  <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;"><li class="list-group-item ipxhover2"><b>3. Cargado del Logo</b><br> <i>Se requiere tu logo en formato JPEG, GIF o PNG con una altura recomendada de 120 pixeles, luego podras centrearlo en el diseño de factura usando las flechas del teclado (no use el mouse)</i></li></a>
                @else     
                  <a href="{{ URL::to('company/invoice_design') }}" style="color:#333333;text-decoration:none;"><li class="list-group-item ipxhover1"><b>3. Cargado del Logo</b><br> <i>Se requiere tu logo en formato JPEG, GIF o PNG con una altura recomendada de 120 pixeles, luego podras centrearlo en el diseño de factura usando las flechas del teclado (no use el mouse)</i></li></a>           
                @endif

                </ul>

              </div>
            </div>              

      </div>


      <div style="padding-left:40px;padding-right:40px;display:none;min-height:130px" id="proPlanWorking">
        <h3>{{ trans('texts.working') }}...</h3>
        <div class="progress progress-striped active">
          <div class="progress-bar"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
        </div>
      </div>

      <div style="background-color: #fff; padding-right:20px;padding-left:20px; display:none" id="proPlanSuccess">
        &nbsp;<br/>
        {{ trans('texts.pro_plan_success') }}
        <br/>&nbsp;
      </div>

       <div class="modal-footer" style="margin-top: 0px" id="proPlanFooter">
          <button type="button" class="btn btn-default" data-dismiss="modal">CERRAR</button>          
          <button type="button" class="btn btn-primary" id="proPlanButton" onclick="submitPlan()">ACEPTA QUE REVISO LOS DATOS</button>                    
       </div>     
      </div>
    </div>
  </div>


<div class="modal fade" id="proPlanModal2" tabindex="-1" role="dialog" aria-labelledby="proPlanModalLabel2" aria-hidden="true">
    <div class="modal-dialog medium-dialog">
      <div class="modal-content">
        <div class="modal-header"style="padding-bottom:10px!important;background-color:#016797!important;">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="proPlanModalLabel2">Examen de Código de Control</h4>
        </div>

        <div style="background-color: #fff; padding-left: 16px; padding-right: 16px" id="proPlanDiv2">

            
                {{ Former::open('account/go_pro2')->addClass('signUpForm2')->rules(array(
                      'dia'     => 'between:1,31',
                      'mes'      => 'between:1,12',
                      'anio'    => 'between:1980,2015',

                )); }}
                <div class="row">
                    <div style="display:none">
                      {{ Former::text('path')->value(Request::path()) }}
                    </div>
                    <table class="table" style="width:100%">
                          <tr>
                            <td>{{ Former::label('Llave de dosificación') }}</td>       
                          </tr>
                    </table>
                    <div class="col-md-11">
                    {{ Former::label('') }}
                    {{ Former::text('llave')->label(' ') }} 
                    </div>
                    <div class="col-md-1">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-1">
                    </div>
                    <table class="table" style="width:100%">
                          <tr>
                            <td>{{ Former::label('Datos de la Nota Fiscal') }}</td>       
                          </tr>
                    </table>

                    <div class="col-md-11">
                        {{ Former::label('') }}
                        {{ Former::text('nroAutorizacion')->label('Núm. de Autorización') }}   

                        {{ Former::text('nroFactura')->label('Número de Factura') }}
                        {{ Former::text('nit_ci')->label('Nit/CI de Cliente') }}
                        
                        <div class="row">
                          <div class="col-md-1">                      
                          </div>
                          <div class="col-md-2" style="padding-left:62px;padding-top:8px;margin-left:57px;">                      
                          Fecha
                          </div>

                          <div class="col-md-3" style="margin-left:-59px;">                      
                          {{ Former::number('dia')->label(' ')->placeholder('día') }} 
                          </div>
                          <div class="col-md-3">
                          {{ Former::number('mes')->label('/')->placeholder('mes') }}   
                          </div>
                          <div class="col-md-3">
                          {{ Former::number('anio')->label('/')->placeholder('año') }}   
                          </div>
                        </div>
                        {{ Former::text('monto')->label('Monto Facturado') }}   

        
                       
                        {{ Former::close() }}


                    </div>
            </div>              

      </div>

      <div style="padding-left:40px;padding-right:40px;display:none;min-height:130px" id="proPlanWorking2">
        <h3>{{ trans('texts.working') }}...</h3>
        <div class="progress progress-striped active">
          <div class="progress-bar"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
        </div>
      </div>

      <div style="background-color: #fff; padding-right:20px;padding-left:20px; display:none" id="proPlanSuccess2">
        {{-- trans('texts.pro_plan_success') --}}

        <div class="row">
            <div style="display:none" class="col-md-3" id="showcc">
            {{ Former::text('cc')->label('Código de Control')->style('color: #A52714') }}
            </div>
           <div style="display:none; color: #A52714;" class="col-md-8" id="showerrorcc">
            {{ 'Favor llenar los campos correctamente.' }}
           </div>
        </div>
      </div>

       <div class="modal-footer" style="margin-top: 0px" id="proPlanFooter2">
          <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('texts.close') }}</button>          
          <button type="button" class="btn btn-primary" id="proPlanButton2" onclick="submitProPlan2()">{{ trans('texts.send') }}</button>                    
       </div>     
      </div>
    </div>
  </div>
@endif

<p>&nbsp;</p>

</body>


<script type="text/javascript">

  function setTheme(id)
  {
    $('#theme_id').val(id);
    $('form.themeForm').submit();
  }

  @if (!Auth::check() || !Auth::user()->registered)
  function validateSignUp(showError) 
  {
    var isFormValid = true;
    $(['nit','name','first_name','last_name','username','email','password']).each(function(i, field) {
      var $input = $('form.signUpForm #new_'+field),
      val = $.trim($input.val());
      var isValid = val && val.length >= (field == 'password' ? 6 : 1);
      if (isValid && field == 'email') {
        isValid = isValidEmailAddress(val);
      }
      if (isValid) {
        $input.closest('div.form-group').removeClass('has-error').addClass('has-success');
      } else {
        isFormValid = false;
        $input.closest('div.form-group').removeClass('has-success');
        if (showError) {
          $input.closest('div.form-group').addClass('has-error');
        }
      }
    });

    $('#saveSignUpButton').prop('disabled', !isFormValid);

    return isFormValid;
  }

  function validateServerSignUp2()
  {
    location.reload();
  }
  function validateServerSignUp()
  {
    if (!validateSignUp(true)) {
      return;
    }

    $('#signUpDiv, #signUpFooter').hide();
    $('#working').show();

    $.ajax({
      type: 'POST',
      url: '{{ URL::to('signup/validate') }}',
      data: 'email=' + $('form.signUpForm #new_email').val(),
      success: function(result) { 
        if (result == 'available') {            
          submitSignUp();
        } else {
          $('#errorTaken').show();
          $('form.signUpForm #new_email').closest('div.form-group').removeClass('has-success').addClass('has-error');
          $('#signUpDiv, #signUpFooter').show();
          $('#working').hide();
        }
      }
    });     
  }

  function submitSignUp() {
    $.ajax({
      type: 'POST',
      url: '{{ URL::to('signup/submit') }}',
      data: 'new_email=' + encodeURIComponent($('form.signUpForm #new_email').val()) + 
      '&new_password=' + encodeURIComponent($('form.signUpForm #new_password').val()) + 
      '&new_first_name=' + encodeURIComponent($('form.signUpForm #new_first_name').val()) + 
      '&new_last_name=' + encodeURIComponent($('form.signUpForm #new_last_name').val()) +
      '&new_nit=' + encodeURIComponent($('form.signUpForm #new_nit').val()) + 
      '&new_name=' + encodeURIComponent($('form.signUpForm #new_name').val()) + 
      '&new_username=' + encodeURIComponent($('form.signUpForm #new_username').val()) + 

      '&go_pro=' + $('#go_pro').val(),
      success: function(result) { 
        if (result) {
          localStorage.setItem('guest_key', '');
          trackUrl('/signed_up');
          NINJA.isRegistered = true;
          /*
          $('#signUpButton').hide();
          $('#myAccountButton').html(result);                            
          */
        }            
        $('#signUpSuccessDiv, #signUpFooter').show();
        $('#working, #saveSignUpButton').hide();
      }
    });     
  }      

  function checkForEnter(event)
  {
    if (event.keyCode === 13){
      event.preventDefault();         
      validateServerSignUp();
      return false;
    }
  }
  @endif

  function logout(force)
  {
    if (force) {
      NINJA.formIsChanged = false;
    }

    // if (force || NINJA.isRegistered) {            
      window.location = '{{ URL::to('logout') }}';
    // }
    // else {
    //   $('#logoutModal').modal('show');  
    // }
  }

  function showSignUp() {    
    // $('#signUpModal').modal('show');    
  }

  @if (Auth::check())
  var proPlanFeature = false;
  function showProPlan(feature) {
    proPlanFeature = feature;
    $('#PlanModal').modal('show');       
    trackUrl('/view_pro_plan/' + feature);
  }

    function submitProPlan() {
    trackUrl('/submit_pro_plan/' + proPlanFeature);
    if (NINJA.isRegistered) {
      $('#proPlanDiv, #proPlanFooter').hide();
      $('#proPlanWorking').show();

      $.ajax({
        type: 'POST',
        url: '{{ URL::to('account/go_pro') }}',
        success: function(result) { 
          $('#proPlanSuccess, #proPlanFooter').show();
          $('#proPlanWorking, #proPlanButton').hide();
          $('#proPlanWorking, #proPlanButton2').hide();
          window.location = '{{ URL::to('logout') }}';
        }
      });     
    } else {
      $('#proPlanModal').modal('hide');
      $('#go_pro').val('true');
    }
  }

  function submitPlan() {
    trackUrl('/submit_plan/' + proPlanFeature);
    if (NINJA.isRegistered) {
      $('#proPlanDiv, #proPlanFooter').hide();
      $('#proPlanWorking').show();

      $.ajax({
        type: 'POST',
        url: '{{ URL::to('account/go') }}',
        success: function(result) { 
          $('#proPlanSuccess, #proPlanFooter').show();
          $('#proPlanWorking, #proPlanButton').hide();
          $('#proPlanWorking, #proPlanButton2').hide();
          window.location = '{{ URL::to('logout') }}';
        }
      });     
    } else {
      $('#PlanModal').modal('hide');
      $('#go_pro').val('true');
    }
  }


  function submitProPlan2() {
    trackUrl('/submit_pro_plan/' + proPlanFeature);
    if (NINJA.isRegistered) {
      $('#proPlanDiv2, #proPlanFooter2').hide();
      $('#proPlanSuccess2').hide();
      $('#proPlanWorking2').show();

      $.ajax({
        type: 'POST',
        url: '{{ URL::to('account/go_pro2') }}',

        data: 'nroFactura=' + encodeURIComponent($('form.signUpForm2 #nroFactura').val()) + 
      '&nit_ci=' + encodeURIComponent($('form.signUpForm2 #nit_ci').val()) + 
      '&dia=' + encodeURIComponent($('form.signUpForm2 #dia').val()) + 
      '&mes=' + encodeURIComponent($('form.signUpForm2 #mes').val()) + 
      '&anio=' + encodeURIComponent($('form.signUpForm2 #anio').val()) + 
      '&monto=' + encodeURIComponent($('form.signUpForm2 #monto').val()) +
      '&llave=' + encodeURIComponent($('form.signUpForm2 #llave').val()) + 
      '&nroAutorizacion=' + encodeURIComponent($('form.signUpForm2 #nroAutorizacion').val()),

        success: function(result) {

        if (result == 'error') { 

          $('#proPlanWorking2').hide();
          $('#proPlanDiv2, #proPlanFooter2').show();
          $('#proPlanSuccess2, #proPlanFooter2').show();
          $('#showcc').hide();
          $('#showerrorcc').show();

        } 
        else {
              $('#proPlanWorking2').hide();
              $('#proPlanDiv2, #proPlanFooter2').show();
              $('#proPlanSuccess2, #proPlanFooter2').show();
              $('#showcc').show();
              $('#showerrorcc').hide();
              $('#cc').val(result);
              
        }
          
        }
      });     
    } else {
      $('#PlanModal').modal('hide');
      $('#go_pro').val('true');
    }
  }

  @endif

  function hideMessage() {
    $('.alert-info').fadeOut();
    $.get('/hide_message', function(response) {
      console.log('Reponse: %s', response);
    });
  }

  $(function() {
    $('#search').focus(function(){
      if (!window.hasOwnProperty('searchData')) {
        $.get('{{ URL::route('getSearchData') }}', function(data) {             
          window.searchData = true;           
          var datasets = [];
          for (var type in data)
          {       

            var type_new = "";
                if(type.match("Invoices"))
                {                  
                     type_new="Facturas";   
                }
                else
                {
                  if(type.match("Clients"))
                  {
                        type_new="Clientes";
                  }
                  else
                  { 
                    if(type.match("Contacts"))
                    {
                          type_new="Contactos";
                    }
                    else
                    {
                      if(type.match("quotes"))
                      {
                            type_new="Recibos";
                      } 
                      else
                      {
                        type_new = type;
                      }
                    }                  
                  }
                }   
            if (!data.hasOwnProperty(type)) continue;               
            datasets.push({
              name: type,
              header: '&nbsp;<b>' + type_new  + '</b>',                 
              local: data[type]
            });                             
          }
          if (datasets.length == 0) {
            return;
          }
          $('#search').typeahead(datasets).on('typeahead:selected', function(element, datum, name) {
            var type = name == 'Contacts' ? 'clients' : name.toLowerCase();
            window.location = '{{ URL::to('/') }}' + '/' + type + '/' + datum.public_id;
          }).focus().typeahead('setQuery', $('#search').val());             
        });
      }
    });


    if (isStorageSupported()) {
      @if (Auth::check() && !Auth::user()->registered)
      localStorage.setItem('guest_key', '{{ Auth::user()->password }}');
      @endif
    }

    @if (!Auth::check() || !Auth::user()->registered)
    validateSignUp();

    $('#signUpModal').on('shown.bs.modal', function () {
      trackUrl('/view_sign_up');
      $(['nit','name','first_name','last_name','username','email','password']).each(function(i, field) {
        var $input = $('form.signUpForm #new_'+field);
        if (!$input.val()) {
          $input.focus();             
          return false;
        }
      });
    })
    @endif

    @yield('onReady')

  });

</script>  


@stop