
<iframe id="theFrame" style="display:none" frameborder="1" width="100%" height="{{ isset($pdfHeight) ? $pdfHeight : 792 }}px"></iframe>
<div class="container"  style=" display: block;margin-left: 64px;margin-right: 64px;">
  <canvas id="theCanvas" style="width:85%;border:solid 1px #CCCCCC;"></canvas>
</div>


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
  logoImages.imageLogoHeight1 = 40;

  logoImages.imageLogo2 = "{{ HTML::image_data('images/report_logo2.jpg') }}";
  logoImages.imageLogoWidth2 =325/2;
  logoImages.imageLogoHeight2 = 81/2;

  logoImages.imageLogo3 = "{{ HTML::image_data('images/report_logo3.jpg') }}";
  logoImages.imageLogoWidth3 =325/2;
  logoImages.imageLogoHeight3 = 81/2;

  @if (file_exists($account->getLogoPath()))
  if (window.invoice) {
    invoice.image = "{{ HTML::image_data($account->getLogoPath()) }}";
    invoice.imageWidth = {{ $account->getLogoWidth() }};
    invoice.imageHeight = {{ $account->getLogoHeight() }};
  }
  @endif  

  var NINJA = NINJA || {};
  NINJA.primaryColor = "{{ $account->primary_color }}";
  NINJA.secondaryColor = "{{ $account->secondary_color }}";

  var invoiceLabels = {{ json_encode($account->getInvoiceLabels()) }};

  var isRefreshing = false;
  var needsRefresh = false;

  function refreshPDF() {
    if ({{ Auth::check() && Auth::user()->force_pdfjs ? 'true' : 'false' }} && (isFirefox || (isChrome && !isChromium))) {
      var string = getPDFString();
      if (!string) return;
      $('#theFrame').attr('src', string).show();    
    } else {      
      if (isRefreshing) {
        needsRefresh = true;
        return;
      }
      var string = getPDFString();
      if (!string) return;
      isRefreshing = true;
      var pdfAsArray = convertDataURIToBinary(string);  
      PDFJS.getDocument(pdfAsArray).then(function getPdfHelloWorld(pdf) {

        pdf.getPage(1).then(function getPageHelloWorld(page) {
          var scale = 6;
          var viewport = page.getViewport(scale);

          var canvas = document.getElementById('theCanvas');
          var context = canvas.getContext('2d');
          canvas.height = viewport.height;
          canvas.width = viewport.width;

          page.render({canvasContext: context, viewport: viewport});
          $('#theCanvas').show();
          isRefreshing = false;
          if (needsRefresh) {
            needsRefresh = false;
            refreshPDF();
          }
        });
      }); 
    }
  }

  function showMoreDesigns() {
    trackUrl('/view_more_designs');
    $('#moreDesignsModal').modal('show');
  }

  function buyDesigns() {
    window.open('{{ Utils::isNinjaDev() ? '' : NINJA_URL }}/license?return_url=' + window.location + '&affiliate_key={{ DESIGNS_AFFILIATE_KEY }}&product_id={{ PRODUCT_INVOICE_DESIGNS }}');
  }

</script>