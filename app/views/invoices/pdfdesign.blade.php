@if ($invoice)

  <iframe id="theFrame" style="display:none" frameborder="1" width="100%" height="{{ isset($pdfHeight) ? $pdfHeight : 792 }}px"></iframe>
  <canvas id="theCanvas" style="display:none;width:95%;border:solid 1px #CCCCCC;"></canvas>

@endif

<script type="text/javascript">
function printCanvas() {  
    var dataUrl = document.getElementById("theCanvas").toDataURL();
    var printWin = window.open('','','width=600,height=500');
    printWin.document.open();
    printWin.document.write("<img width='99.5%'  src='"+dataUrl+"'/>");
    printWin.document.close();
    printWin.focus();
    printWin.print();
    printWin.close();
}

  window.logoImages = {};

  logoImages.logofooter = "{{ HTML::image_data('images/logofooter.jpg') }}";
  logoImages.imageLogoWidthf =100;
  logoImages.imageLogoHeightf = 13;
  
  logoImages.imageLogo1 = "{{ HTML::image_data('images/report_logo1.jpg') }}";
  logoImages.imageLogoWidth1 =120;
  logoImages.imageLogoHeight1 = 40

  var invoiceLabels = {{ json_encode($account->getInvoiceLabels()) }};

  var isRefreshing = false;
  var needsRefresh = false;

  function refreshPDF() {
    if ({{ Auth::check() }}) {
      var string = getPDFString();
      if (!string) return;
      $('#theFrame').attr('src', string).show();    
    } 
  }

</script>