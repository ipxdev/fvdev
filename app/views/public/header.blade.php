@extends('master')

@section('head')    

<link href="{{ asset('built.public.css') }}?no_cache={{ NINJA_VERSION }}" rel="stylesheet" type="text/css"/>
<script src="{{ asset('js/built.public.js') }}?no_cache={{ NINJA_VERSION }}" type="text/javascript"></script>

<style>
  .hero {
    background-image: url({{ asset('/images/hero-bg-1.jpg') }});
  }
  .hero-about {
    background-image: url({{ asset('/images/hero-bg-3.jpg') }});
  }
  .hero-plans {
    background-image: url({{ asset('/images/hero-bg-plans.jpg') }});
  }
  .hero-contact {
    background-image: url({{ asset('/images/hero-bg-contact.jpg') }});
  }
  .hero-features {
    background-image: url({{ asset('/images/hero-bg-3.jpg') }});
  }
  .hero-secure {
    background-image: url({{ asset('/images/hero-bg-secure-pay.jpg') }});
  }
 .hero-faq {
    background-image: url({{ asset('/images/hero-bg-faq.jpg') }});
  }   
  .hero-testi {
    background-image: url({{ asset('/images/hero-bg-testi.jpg') }});
  }   


</style>

@stop

@section('body')



{{ Form::open(array('url' => 'get_started', 'id' => 'startForm')) }}
{{ Form::hidden('guest_key') }}
{{ Form::close() }}

<script>
  if (isStorageSupported()) {
    $('[name="guest_key"]').val(localStorage.getItem('guest_key'));          
  }

  @if (isset($invoiceNow) && $invoiceNow)
  getStarted();
  @endif

  function isStorageSupported() {
    if ('localStorage' in window && window['localStorage'] !== null) {
      var storage = window.localStorage;
    } else {
      return false;
    }
    var testKey = 'test';
    try {
      storage.setItem(testKey, '1');
      storage.removeItem(testKey);
      return true;
    } catch (error) {
      return false;
    }    
  }

  function getStarted() {
    $('#startForm').submit();
    return false;
  }
</script>
@if (!isset($hideHeader) || !$hideHeader)

@else
<div class="navbar" style="margin-bottom:0px">
  <div class="container">
      <div class="navbar-header">
        <a class="navbar-brand" href="http://www.facturavirtual.com.bo/"><img src="{{ asset('images/logo-factura-virtual.png') }}"></a>
      </div>
    </div>
  </div>
@endif


@yield('content')   


<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        
          <div class="form-group">
          
 


<script type="text/javascript">

</script>

          </div>
        </form>

      </div>

</div>
</div>
</div>
</div>
</footer>

<script type="text/javascript">
  jQuery(document).ready(function($) {   
   $('.valign').vAlign();  
 });
</script>


@stop