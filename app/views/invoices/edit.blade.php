@extends('header')

@section('head')
	@parent

		<script src="{{ asset('js/pdf_viewer.js') }}" type="text/javascript"></script>
		<script src="{{ asset('js/compatibility.js') }}" type="text/javascript"></script>
		
		<script src="{{ asset('js/png.js') }}" type="text/javascript"></script>
		<script src="{{ asset('js/zlib.js') }}" type="text/javascript"></script>
		<script src="{{ asset('js/addimage.js') }}" type="text/javascript"></script>
		<script src="{{ asset('js/png_support.js') }}" type="text/javascript"></script>
@stop

@section('content')
	
	@if ($invoice && $invoice->id)
		@if (!$invoice->is_recurring)
			<ol class="breadcrumb">
			<li>{{ link_to(($entityType == ENTITY_QUOTE ? 'quotes' : 'invoices'), trans('texts.' . ($entityType == ENTITY_QUOTE ? 'quotes' : 'invoice'))) }}</li>
			</ol> 
		@else
			<ol class="breadcrumb">
			<li>{{ link_to(($entityType == ENTITY_QUOTE ? 'quotes' : 'invoices'), trans('Modelo de Factura Recurrente')) }}</li>
			</ol> 
		@endif
	@else

		<ol class="breadcrumb">
			<li class='active'>Nueva Factura</li>
			<li class='active'><span  class="ipxtitle">{{ Auth::user()->branch->name }}</span></li>
		</ol>  

	@endif

	{{ Former::open($url)->method($method)->addClass('warn-on-exit')->rules(array(
		'client' => 'required',
		'invoice_date' => 'required',
		'product_key' => 'max:20',
	)) }}	

	<input type="submit" style="display:none" name="submitButton" id="submitButton">

	<div data-bind="with: invoice">

    <div class="row" style="min-height:10px">


    	<div class="col-md-5" id="col_1">

    		@if ($invoice && $invoice->id)
				<div class="form-group">

				</div>    				
				<div style="display:none">
    		@endif

			<div class="control-label col-lg-2 col-sm-2" style="margin-left: -12px;"> 		
			{{ Former::label('cliente') }}
			</div>
			<div class="col-lg-10 col-sm-10">
			{{ Former::select('client')->placeholder('Escriba nombre del cliente...')->raw()->data_bind("dropdown: client")->addGroupClass('client_select closer-row') }}
			</div>

			<div class="form-group" style="margin-top: 5px">
 				<div class="col-md-3" style="margin-top: 12px;">
					<span style="padding-left: 65px;font-weight:bold;" data-bind="text: $root.clientLinkTextNit"></span>	
				</div> 
				<div class="col-md-9" style="margin-top: 10px; margin-left: -20px;">
					<a id="createClientLink" style="font-size: 16px; padding-left: 2px;" class="pointer" data-bind="click: $root.showClientForm, text: $root.clientLinkText"></a>
					<span class='ipxlittle' data-bind="click: $root.showClientForm, text: $root.clientLinkTextEdit"></span>
				</div>

				<div class="col-md-3" style="margin-top: 8px;">
					<span style="padding-left: 4px;font-weight:bold;" data-bind="text: $root.clientLinkTextRz"></span>	
				</div> 
				<div class="col-md-9" style="margin-top: 6px; margin-left: -20px;">
					<a id="createClientLink" style="font-size: 16px;" class="pointer" data-bind="click: $root.showClientForm, text: $root.clientLinkTextrz"></a>
					<span class='ipxlittle' data-bind="click: $root.showClientForm, text: $root.clientLinkTextEdit"></span>					
				</div>

			</div>

			@if ($invoice && $invoice->id)
				</div>
			@endif
@if (!$invoice)
			<div class="form-group" style="margin-top: -30px">
				<div class="col-md-3" style="padding-left: 44px;padding-top: 5px;">
					<span style="padding-left: 4px;font-weight:bold;" data-bind="text: $root.emailtittle"></span>
				</div>
				<div class="col-md-8" style="padding-left: 30px;padding-top: -10px;">
					<div data-bind="with: client">
						<div style="display:none" class="form-group" data-bind="visible: contacts().length > 0 &amp;&amp; contacts()[0].email(), foreach: contacts">
							<label for="test" class="checkbox" data-bind="attr: {for: $index() + '_check'}">
								<input type="checkbox" value="1" data-bind="checked: send_invoice, attr: {id: $index() + '_check'}">
									<span data-bind="html: email.display"/>
							</label>
						</div>				
					</div>
				</div>
			</div>
@endif			
		</div>
		@if (!$invoice)
		<div class="col-md-4" id="col_2">

			<div data-bind="visible: !is_recurring()">

				{{ Former::text('invoice_date')->data_bind("datePicker: invoice_date, valueUpdate: 'afterkeydown'")->label(trans("texts.{$entityType}_date"))
							->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT))->append('<i class="glyphicon glyphicon-calendar" onclick="toggleDatePicker(\'invoice_date\')"></i>') }}

				{{ Former::text('due_date')->data_bind("datePicker: due_date, valueUpdate: 'afterkeydown'")->label(trans("texts.due_datev"))
							->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT))->append('<i class="glyphicon glyphicon-calendar" onclick="toggleDatePicker(\'due_date\')"></i>') }}	

			</div>
			@if ($entityType == ENTITY_INVOICE)
				<div data-bind="visible: is_recurring" style="display: none">
					{{ Former::select('frequency_id')->options($frequencies)->data_bind("value: frequency_id") }}
					{{ Former::text('start_date')->data_bind("datePicker: start_date, valueUpdate: 'afterkeydown'")
								->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT))->append('<i class="glyphicon glyphicon-calendar" onclick="toggleDatePicker(\'start_date\')"></i>') }}
					{{ Former::text('end_date')->data_bind("datePicker: end_date, valueUpdate: 'afterkeydown'")
								->data_date_format(Session::get(SESSION_DATE_PICKER_FORMAT))->append('<i class="glyphicon glyphicon-calendar" onclick="toggleDatePicker(\'end_date\')"></i>') }}
				</div>
				@if ($invoice && $invoice->recurring_invoice_id)
					<div class="pull-right" style="padding-top: 6px">
						Modelo de factura {{ link_to('/invoices/'.$invoice->recurring_invoice_id, 'recurring invoice') }}
					</div>
				@else 
		
				@endif
			@endif
			
		</div>

		<div class="col-md-3" id="col_2">
			{{ Former::text('discount')->label('Descuento')->data_bind("value: discount, valueUpdate: 'afterkeydown'")->append('%') }}
			
			<div data-bind="visible: invoice_status_id() < CONSTS.INVOICE_STATUS_SENT">
				{{-- Former::checkbox('recurring')->label(' ')->text('Activar recurrencia')->data_bind("checked: is_recurring")
					->inlineHelp($invoice && $invoice->last_sent_date ? 'Factura Enviada ' . Utils::dateToString($invoice->last_sent_date) : '') --}}
			</div>

		</div>
		@endif

	</div>

	{{ Former::hidden('data')->data_bind("value: ko.mapping.toJSON(model)") }}	

	@if (!$invoice)
	<div class="table-responsive">
	<table class="table invoice-table">
		<thead>
			<tr>
				<th style="min-width:32px;" class="hide-border"></th>
				<th style="min-width:160px">{{ trans('texts.item') }}</th>
				<th style="width:100%">{{ trans('texts.description') }}</th>
				<th style="min-width:120px">{{ trans('texts.unit_cost') }}</th>
				<th style="min-width:120px">{{ trans('texts.quantity') }}</th>
				<th style="min-width:120px;">{{ trans('texts.line_total') }}</th>
				<th style="min-width:32px;" class="hide-border"></th>
			</tr>
		</thead>
		<tbody data-bind="sortable: { data: invoice_items, afterMove: onDragged }">
			<tr data-bind="event: { mouseover: showActions, mouseout: hideActions }" class="sortable-row">
				<td class="hide-border td-icon">
					<i style="display:none" data-bind="visible: actionsVisible() &amp;&amp; $parent.invoice_items().length > 1" class="fa fa-sort"></i>
				</td>
				<td>	            	
					{{ Former::text('product_key')->useDatalist($products->toArray(), 'product_key','cost')->onkeyup('onItemChange()')
					->raw()->data_bind("value: product_key, valueUpdate: 'afterkeydown'")->addClass('datalist') }}
				</td>
				<td>
					<textarea data-bind="value: wrapped_notes, valueUpdate: 'afterkeydown'" rows="1" cols="60" style="resize: none;" class="form-control word-wrap"></textarea>
				</td>
				<td>
					<input onkeyup="onItemChange()"  min="0" step="any" data-bind="value: prettyCost, valueUpdate: 'afterkeydown'" style="text-align: right" class="form-control"//>
				</td>
				<td>
					<input onkeyup="onItemChange()"  min="0" step="any" data-bind="value: prettyQty, valueUpdate: 'afterkeydown'" style="text-align: right" class="form-control"//>
				</td>

				<td data-bind="visible: $root.invoice_item_discount.show">
					<input data-bind="value: prettyDisc, valueUpdate: 'afterkeydown'" style="text-align: right" class="form-control"//>
				</td>

				<td style="text-align:right;padding-top:9px !important">
					<div class="line-total" data-bind="text: totals.total"></div>
				</td>
				<td style="cursor:pointer" class="hide-border td-icon">
					&nbsp;<i style="display:none" data-bind="click: $parent.removeItem, visible: actionsVisible() &amp;&amp; $parent.invoice_items().length > 1" class="fa fa-minus-circle redlink" title="Remove item"/>
				</td>
			</tr>
		</tbody>


		<tfoot>
			<tr>
				<td class="hide-border"/>
				<td colspan="2" rowspan="6" style="vertical-align:top">
					<br/>
					{{ Former::textarea('public_notes')->data_bind("value: wrapped_notes, valueUpdate: 'afterkeydown'")
					->label(false)->maxlength('125')->placeholder(trans('texts.note_to_client'))->style('resize: none') }}			
					{{ Former::textarea('terms')->data_bind("value: wrapped_terms, valueUpdate: 'afterkeydown'")
					->label(false)->maxlength('125')->placeholder(trans('texts.invoice_terms'))->style('resize: none')
					->addGroupClass('less-space-bottom') }}
				</td>
				<td style="display:none" data-bind="visible: $root.invoice_item_discount.show"/>	        	
				<td colspan="2">{{ trans('texts.subtotal_i') }}</td>
				<td style="text-align: right"><span data-bind="text: totals.subtotal"/></td>
			</tr>

			<tr style="display:none" data-bind="visible: discount() > 0 || $root.invoice_item_discount.show">
				<td class="hide-border" colspan="3"/>
				<td style="display:none" data-bind="visible: $root.invoice_item_discount.show"/>	        	
				<td colspan="2">{{ trans('texts.discount') }}</td>
				<td style="text-align: right"><span data-bind="text: totals.discounted"/></td>
			</tr>

			<tr>
				<td class="hide-border" colspan="3"/>
				<td style="display:none" data-bind="visible: $root.invoice_item_discount.show"/>
				<td colspan="2"><b>{{ trans($entityType == ENTITY_INVOICE ? 'texts.total_i' : 'texts.total') }}</b></td>
				<td style="text-align: right"><span data-bind="text: totals.total"/></td>
			</tr>

		</tfoot>


	</table>
	</div>
	<p>&nbsp;</p>
	@endif

	<div class="form-actions">

		<div style="display:none">
			{{ Former::populateField('entityType', $entityType) }}
			{{ Former::text('entityType') }}
			{{ Former::text('action') }}
				
			@if ($invoice && $invoice->id)
				{{ Former::populateField('id', $invoice->public_id) }}
				{{ Former::text('id') }}		
			@endif
		</div>

  			@if (!$invoice)			
					@if (Auth::user()->isPro())

					<div data-bind="visible: !is_recurring()">
					{{ Button::success(trans("texts.save_{$entityType}"), array('id' => 'saveButton', 'onclick' => 'onSaveClick()')) }}
					&nbsp;&nbsp;&nbsp;
					<div id="primaryActions" style="text-align:left" class="btn-group">
					{{ Button::primary(trans("texts.save_pay_{$entityType}"), array('id' => 'save_pay_button', 'onclick' => 'onsavepayClick()')); }}		
					<button class="btn-primary btn dropdown-toggle" type="button" data-toggle="dropdown"> 
					<span class="caret"></span>
					</button>
					 <ul class="dropdown-menu">
						<li><a href="javascript:onsavepaycreditClick()" id="saveButton">{{ trans("texts.save_pay_credit_{$entityType}") }}</a></li>
					</ul> 
					</div>
					{{ Button::normal(trans("texts.save_email_{$entityType}"), array('id' => 'email_button', 'onclick' => 'onSaveEmailClick()'))->append_with_icon('send'); }}		
					</div>

					<div data-bind="visible: is_recurring" style="display: none">
					{{ Button::success('Emitir Factura Recurrente', array('id' => 'saveButton', 'onclick' => 'onSaveClick()')) }}
					</div>

			
					@endif
			@endif
			
			@if ($invoice && $invoice->id && $entityType == ENTITY_INVOICE)
				@if (!$invoice->is_recurring)	
					@if ($invoice->invoice_status_id  != '5')
						{{ Button::primary(trans('texts.enter_payment'), array('onclick' => 'onPaymentClick()'))->append_with_icon('usd'); }}
					@endif		
					{{ Button::success(trans('texts.print_invoice'), array('onclick' => 'printCanvas()'))->append_with_icon('print'); }}		
					{{ Button::normal(trans("texts.email_{$entityType}"), array('id' => 'email_button', 'onclick' => 'onEmailClick()'))->append_with_icon('send'); }}		
					{{ Button::primary(trans('texts.download_pdf'), array('onclick' => 'onDownloadClick()'))->append_with_icon('download-alt'); }}	
				@endif
			@endif


	</div>
	
	<p>&nbsp;</p>

	@include('invoices.pdf', ['account' => Auth::user()->account])


	<div class="modal fade" id="clientModal" tabindex="-1" role="dialog" aria-labelledby="clientModalLabel" aria-hidden="true">
	  <div class="modal-dialog large-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	        <h4 class="modal-title" id="clientModalLabel">{{ trans('texts.client') }}</h4>
	      </div>

	      <div class="container" style="width: 100%">
			<div style="background-color: #fff" class="row" data-bind="with: client">
				<div class="col-md-12" style="margin-left:0px;margin-right:0px" >

					{{ Former::legend('organization') }}
	                {{ Former::text('nit')->label('NIT')->data_bind("value: nit, valueUpdate: 'afterkeydown', attr { placeholder: nit.placeholder }") }}				
					{{ Former::text('business_name')->label('Razón Social')->data_bind("value: business_name, valueUpdate: 'afterkeydown', attr { placeholder: business_name.placeholder }") }}

				</div>
			</div>
		</div>

	     <div class="modal-footer" style="margin-top: 0px">
	      	<button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('texts.cancel') }}</button>
	        <button id="clientDoneButton" type="button" class="btn btn-primary" data-bind="click: $root.clientFormComplete">{{ trans('texts.done') }}</button>	      	
	     </div>
	  		
	    </div>
	  </div>
	</div>

	{{ Former::close() }}


	</div>

	<script type="text/javascript">

	document.onkeypress=function(e){
	var esIE=(document.all);
	var esNS=(document.layers);
	tecla=(esIE) ? event.keyCode : e.which;
	if(tecla==13){
		return false;
	  }
	}

	function callkeydownhandler(evnt) {
		refreshPDF();
	}
	if (window.document.addEventListener) {
	   window.document.addEventListener("keydown", callkeydownhandler, false);
	} else {
	   window.document.attachEvent("onkeydown", callkeydownhandler);
	}

	document.oncontextmenu = function(){return false;}


	$(function() {

		$('[rel=tooltip]').tooltip();

		$('#invoice_date, #due_date, #start_date, #end_date').datepicker();

		@if ($client && !$invoice)
			$('input[name=client]').val({{ $client->public_id }});
		@endif
		
		var $input = $('select#client');
		$input.combobox().on('change', function(e) {
			var clientId = parseInt($('input[name=client]').val(), 10);		
			if (clientId > 0) { 
				model.loadClient(clientMap[clientId]);				
			} else {
				model.loadClient($.parseJSON(ko.toJSON(new ClientModel())));
			}
			refreshPDF();
		});		

		$('#terms, #public_notes, #invoice_date, #due_date, #discount, #recurring').change(function() {
			setTimeout(function() {
				refreshPDF();
			}, 1);
		});

		$('.client_select input.form-control').focus();			
		
		$('#clientModal').on('shown.bs.modal', function () {
			$('#nit').focus();			
		}).on('hidden.bs.modal', function () {
			if (model.clientBackup) {
				model.loadClient(model.clientBackup);
				refreshPDF();
			}
		})

		$('#relatedActions > button:first').click(function() {
			onPaymentClick();
		});

		$('#primaryActions > button:first').click(function() {
			onSaveClick();
		});

		$('label.radio').addClass('radio-inline');

		applyComboboxListeners();
		
		@if ($client)
			$input.trigger('change');
		@else 
			refreshPDF();
		@endif

		var client = model.invoice().client();
		setComboboxValue($('.client_select'), 
			client.public_id(), 
			client.business_name.display());
		
	});	

	function applyComboboxListeners() {
		var selectorStr = '.invoice-table input, .invoice-table select, .invoice-table textarea';		
		$(selectorStr).off('blur').on('blur', function() {
			refreshPDF();
		});
		var newkey;
		@if (Auth::user()->account->fill_products)
			$('.datalist').on('input', function() {			
				var key = $(this).val();
				for (var i=0; i<products.length; i++) {
					var product = products[i];
					newkey = key.toUpperCase();

					if (product.product_key == newkey) {
						var model = ko.dataFor(this);					
						model.notes(product.notes);
						model.cost(accounting.formatMoney(product.cost, " ", 1, ",", "."));
						model.qty(1);
						break;
					}
				}
				onItemChange();
				refreshPDF();
			});
		@endif

	}

	function createInvoiceModel() {
		var invoice = ko.toJS(model).invoice;		
		invoice.is_quote = {{ $entityType == ENTITY_QUOTE ? 'true' : 'false' }};
    	return invoice;
	}

	function getPDFString() {
		var invoice = createInvoiceModel();
		var design  = getDesignJavascript();
		var doc = generatePDF(invoice, design, getLogoJavascript(), getLogoXJavascript(), getLogoYJavascript());
		return doc.output('datauristring');
	}

	function getDesignJavascript() {
		return invoiceDesigns[0].javascript;
	}

	function getLogoJavascript() {
      return invoiceDesigns[0].logo; 
    }

    function getLogoXJavascript() {
        return invoiceDesigns[0].x;
      }

    function getLogoYJavascript() {
        return invoiceDesigns[0].y;
      }

	function onDownloadClick() {
		trackUrl('/download_pdf');
		var invoice = createInvoiceModel();
		var design  = getDesignJavascript();
		if (!design) return;
		var doc = generatePDF(invoice, design, getLogoJavascript(), getLogoXJavascript(), getLogoYJavascript());
		doc.save('Factura Num: ' + invoice.invoice_number +', '+ invoice.invoice_date + '.pdf');
	}

	function onSaveEmailClick() {
		if (confirm('{{ trans("texts.confirm_save_email_$entityType") }}')) {		
			submitAction('email');
		}
	}
	function onEmailClick() {
		if (confirm('{{ trans("texts.confirm_email_$entityType") }}')) {		
			submitAction('email');
		}
	}

	function onsavepayClick() {
		if (confirm('{{ trans("texts.confirm_savepay_$entityType") }}')) {		
			submitAction('savepay');
		}
	}
	function onsavepaycreditClick() {
		if (confirm('{{ trans("texts.confirm_savepay_credit_$entityType") }}')) {		
			submitAction('savepaycredit');
		}
	}

	function onSaveClick() {
		if (model.invoice().is_recurring()) {
			if (confirm('{{ trans("texts.confirm_recurring_email_$entityType") }}')) {		
				submitAction('');
			}			
		} else {
			submitAction('save');
		}
	}

	function submitAction(value) {
		$('#action').val(value);
		$('#submitButton').click();		
	}

	function onConvertClick() {
		submitAction('convert');		
	}

	@if ($client && $invoice)
	function onPaymentClick() {
		window.location = '{{ URL::to('payments/create/' . $client->public_id . '/' . $invoice->public_id ) }}';
	}
	@endif

	function ViewModel(data) {
		var self = this;
		self.invoice = ko.observable(data ? false : new InvoiceModel());
		self.tax_rates = ko.observableArray();

		self.loadClient = function(client) {
			ko.mapping.fromJS(client, model.invoice().client().mapping, model.invoice().client);
		}

		self.invoice_item_taxes = ko.observable(false);
		self.invoice_item_discount = ko.observable(false);

		self.invoice_item_discount2 = ko.observable(true);

		self.mapping = {
		    'invoice': {
		        create: function(options) {
		            return new InvoiceModel(options.data);
		        }
		    },
		    'tax_rates': {
		    	create: function(options) {
		    		return new TaxRateModel(options.data);
		    	}
		    },
		}		

		if (data) {
			ko.mapping.fromJS(data, self.mapping, self);
		}


		self.invoice_item_discount.show = ko.computed(function() {
			if (self.invoice_item_discount()) {

				self.invoice_item_discount2(false);
				return true;
			}
			self.invoice_item_discount2(true);
			return false;
		});


		self.invoice_item_taxes.show = ko.computed(function() {
			if (self.tax_rates().length > 2 && self.invoice_item_taxes()) {
				return true;
			}
			for (var i=0; i<self.invoice().invoice_items().length; i++) {
				var item = self.invoice().invoice_items()[i];
				if (item.tax_rate() > 0) {
					return true;
				}
			}
			return false;
		});

		self.tax_rates.filtered = ko.computed(function() {
			var i = 0;
			for (i; i<self.tax_rates().length; i++) {
				var taxRate = self.tax_rates()[i];
				if (taxRate.isEmpty()) {
					break;
				}
			}

			var rates = self.tax_rates().concat();
			rates.splice(i, 1);
			return rates;
		});
		

		self.removeTaxRate = function(taxRate) {
			self.tax_rates.remove(taxRate);
			//refreshPDF();
		}

		self.addTaxRate = function(data) {
			var itemModel = new TaxRateModel(data);
			self.tax_rates.push(itemModel);	
			applyComboboxListeners();
		}		


		self.getTaxRate = function(name, rate) {
			for (var i=0; i<self.tax_rates().length; i++) {
				var taxRate = self.tax_rates()[i];
				if (taxRate.name() == name && taxRate.rate() == parseFloat(rate)) {
					return taxRate;
				}			
			}			

			var taxRate = new TaxRateModel();
			taxRate.name(name);
			taxRate.rate(parseFloat(rate));
			if (parseFloat(rate) > 0) taxRate.is_deleted(true);
			self.tax_rates.push(taxRate);
			return taxRate;			
		}		

		self.showClientForm = function() {
			trackUrl('/view_client_form');
			self.clientBackup = ko.mapping.toJS(self.invoice().client);

			$('#clientModal').modal('show');			
		}

		self.clientFormComplete = function() {
			trackUrl('/save_client_form');

			var isValid = true;

			var firstName = $('#first_name').val();
			var lastName = $('#last_name').val();
			var business_name = $('#business_name').val();

			if (self.invoice().client().public_id() == 0) {
				self.invoice().client().public_id(-1);
			}

			refreshPDF();
			model.clientBackup = false;
			$('#clientModal').modal('hide');						
		}	

    	self.emailtittle = ko.computed(function() {
			if (self.invoice().client().public_id())
			{
				var client = self.invoice().client();
				for (var i=0; i<client.contacts().length; i++) {
					var contact = client.contacts()[i];        		
					if (contact.email()) {
						return "Enviar a";
					} 
				}
			}
			else
			{
				return "";
			}
    	});

    	self.clientLinkTextEdit = ko.computed(function() {
			if (self.invoice().client().public_id())
			{
				return "(Editar)";
			}
			else
			{
				return "";
			}
    	});

		self.clientLinkTextNit = ko.computed(function() {
			if (self.invoice().client().public_id())
			{
				return "NIT";
			}
			else
			{
				return "";
			}
    	});

    	self.clientLinkTextRz = ko.computed(function() {
			if (self.invoice().client().public_id())
			{
				return "Razón Social";
			}
			else
			{
				return "";
			}
    	});

		self.clientLinkText = ko.computed(function() {
			if (self.invoice().client().public_id())
			{

				var datos= self.invoice().client().nit();
				return datos;
			}
			else
			{
				return "";
			}
    	});

    	self.clientLinkTextrz = ko.computed(function() {
			if (self.invoice().client().public_id())
			{

				var datos= self.invoice().client().business_name();
				return datos;
			}
			else
			{
				return "";
			}
    	});
	}

	function InvoiceModel(data) {
		var self = this;		
		this.client = ko.observable(data ? false : new ClientModel());		
		self.account = {{ $account }};	
		self.branches = {{ $branches }};	
		this.id = ko.observable('');
		self.discount = ko.observable('');
		self.frequency_id = ko.observable('');
		self.terms = ko.observable('');
		self.public_notes = ko.observable('');		
		self.invoice_date = ko.observable('{{ Utils::today() }}');
		self.invoice_number = ko.observable('');
		self.due_date = ko.observable('');
		self.start_date = ko.observable('{{ Utils::today() }}');
		self.end_date = ko.observable('');
		self.tax_name = ko.observable();
		self.tax_rate = ko.observable();
		self.is_recurring = ko.observable(false);
		self.invoice_status_id = ko.observable(0);
		self.invoice_items = ko.observableArray();
		self.amount = ko.observable(0);
		self.balance = ko.observable(0);

		self.branch_id = ko.observable('');

		self.custom_value1 = ko.observable(0);
		self.custom_value2 = ko.observable(0);
		self.custom_taxes1 = ko.observable(false);
		self.custom_taxes2 = ko.observable(false);
		self.discount_item = ko.observable(0);


		self.mapping = {
			'client': {
		        create: function(options) {
		            return new ClientModel(options.data);
		        }
			},
		    'invoice_items': {
		        create: function(options) {
		            return new ItemModel(options.data);
		        }
		    },
		    'tax': {
		    	create: function(options) {
		    		return new TaxRateModel(options.data);
		    	}
		    },
		}

		self.addItem = function() {
			var itemModel = new ItemModel();
			self.invoice_items.push(itemModel);	
			applyComboboxListeners();			
		}

		if (data) {
			ko.mapping.fromJS(data, self.mapping, self);			
			self.is_recurring(parseInt(data.is_recurring));
		} else {
			self.addItem();
		}

		self._tax = ko.observable();
		this.tax = ko.computed({
			read: function () {
				return self._tax();
			},
			write: function(value) {
				if (value) {
					self._tax(value);								
					self.tax_name(value.name());
					self.tax_rate(value.rate());
				} else {
					self._tax(false);								
					self.tax_name('');
					self.tax_rate(0);
				}
			}
		})

		self.wrapped_terms = ko.computed({
			read: function() {
				$('#terms').height(this.terms().split('\n').length * 30);
				return this.terms();
			},
			write: function(value) {
				value = wordWrapText(value, 300);
				self.terms(value);
				$('#terms').height(value.split('\n').length * 30);
			},
			owner: this
		});


		self.wrapped_notes = ko.computed({
			read: function() {
				$('#public_notes').height(this.public_notes().split('\n').length * 30);
				return this.public_notes();
			},
			write: function(value) {
				value = wordWrapText(value, 300);
				self.public_notes(value);
				$('#public_notes').height(value.split('\n').length * 30);
			},
			owner: this
		});


		self.removeItem = function(item) {
			self.invoice_items.remove(item);
			refreshPDF();
		}


		this.totals = ko.observable();

		this.totals.rawSubtotal = ko.computed(function() {
		    var total = 0;
		    for(var p=0; p < self.invoice_items().length; ++p) {
		    	var item = self.invoice_items()[p];
	        total += item.totals.rawTotal();
		    }
		    return total;
		});

		this.totals.subtotal = ko.computed(function() {
		    var total = self.totals.rawSubtotal();
		    return total > 0 ? formatMoney(total, 1) : '';
		});
		
		this.totals.discSubtotal = ko.computed(function() {
		    var total = 0;
		    for(var p=0; p < self.invoice_items().length; ++p) {
		    	var item = self.invoice_items()[p];
	        total += item.totals.discTotal();
		    }
		    return total;
		});

		this.totals.rawDiscounted = ko.computed(function() {
			return roundToTwo(self.totals.rawSubtotal() * (self.discount()/100));			
		});


		this.discount_item = ko.computed(function() {
			return formatMoney(self.totals.discSubtotal(), 1);
		});

		this.totals.rawDiscountedFinal = ko.computed(function() {
			var a = self.totals.rawDiscounted();
			var b = self.totals.discSubtotal();
			var c = NINJA.parseFloat(a) + NINJA.parseFloat(b);

			return roundToTwo(c);			
		});

		this.discountotal = ko.computed(function() {
			return self.totals.rawDiscountedFinal();
		});

		this.totals.discounted = ko.computed(function() {
			return formatMoney(self.totals.rawDiscountedFinal(), 1);
		});

		self.totals.taxAmount = ko.computed(function() {
	    var total = self.totals.rawSubtotal();

	    var discount = parseFloat(self.discount());
	    if (discount > 0) {
	    	total = roundToTwo(total * ((100 - discount)/100));
	    }

	    var customValue1 = roundToTwo(self.custom_value1());
	    var customValue2 = roundToTwo(self.custom_value2());
	    var customTaxes1 = self.custom_taxes1() == 1;
	    var customTaxes2 = self.custom_taxes2() == 1;
	    
	    if (customValue1 && customTaxes1) {
	    	total = NINJA.parseFloat(total) + customValue1;
	    }
	    if (customValue2 && customTaxes2) {
	    	total = NINJA.parseFloat(total) + customValue2;
	    }

			var taxRate = parseFloat(self.tax_rate());
			if (taxRate > 0) {
				var tax = roundToTwo(total * (taxRate/100));			
        		return formatMoney(tax, 1);
        	} else {
        		return formatMoney(0);
        	}
    	});

		this.totals.rawPaidToDate = ko.computed(function() {
			return accounting.toFixed(self.amount(),1) - accounting.toFixed(self.balance(),1);
		});

		this.totals.paidToDate = ko.computed(function() {
			var total = self.totals.rawPaidToDate();
		    return total > 0 ? formatMoney(total, 1) : '';			
		});

		this.totals.total = ko.computed(function() {
	    var total = accounting.toFixed(self.totals.rawSubtotal(),1);	    

	    var discount = parseFloat(self.discount());

	    var discount_item = parseFloat(self.totals.discSubtotal());

	    if (discount > 0) {
	    	total = roundToTwo(total * ((100 - discount)/100));
	    }

	    if (discount_item > 0) {
	    	total = roundToTwo(total - discount_item);
	    }

	    var customValue1 = roundToTwo(self.custom_value1());
	    var customValue2 = roundToTwo(self.custom_value2());
	    var customTaxes1 = self.custom_taxes1() == 1;
	    var customTaxes2 = self.custom_taxes2() == 1;
	    
	    if (customValue1 && customTaxes1) {
	    	total = NINJA.parseFloat(total) + customValue1;
	    }
	    if (customValue2 && customTaxes2) {
	    	total = NINJA.parseFloat(total) + customValue2;
	    }

			var taxRate = parseFloat(self.tax_rate());
			if (taxRate > 0) {
    		total = NINJA.parseFloat(total) + roundToTwo((total * (taxRate/100)));
    	}        	

	    if (customValue1 && !customTaxes1) {
	    	total = NINJA.parseFloat(total) + customValue1;
	    }
	    if (customValue2 && !customTaxes2) {
	    	total = NINJA.parseFloat(total) + customValue2;
	    }
	    
    	var paid = self.totals.rawPaidToDate();
    	if (paid > 0) {
    		total -= paid;
    	}

	    return total != 0 ? formatMoney(total, 1) : '';
  	});

  	self.onDragged = function(item) {
  		refreshPDF();
  	}	
	}

	function ClientModel(data) {
		var self = this;
		self.public_id = ko.observable(0);
		self.nit = ko.observable('');
		self.business_name = ko.observable('');
        self.name = ko.observable('');
		self.contacts = ko.observableArray();

		self.mapping = {
	    	'contacts': {
	        	create: function(options) {
	            	return new ContactModel(options.data);
	        	}
	    	}
		}


		self.business_name.display = ko.computed(function() {
			if (self.name()) {
				return self.name();
			}
		});				
	
		self.business_name.placeholder = ko.computed(function() {
			if (self.business_name()) {
				return self.business_name();
			}
		});	

		self.nit.placeholder = ko.computed(function() {
			if (self.nit()) {
				return self.nit();
			}
			
		});	

		if (data) {
			ko.mapping.fromJS(data, {}, this);
		} 	
	}

	function ContactModel(data) {
		var self = this;
		self.public_id = ko.observable('');
		self.first_name = ko.observable('');
		self.last_name = ko.observable('');
		self.email = ko.observable('');
		self.phone = ko.observable('');		
		self.send_invoice = ko.observable(false);
		self.invitation_link = ko.observable('');		

		self.email.display = ko.computed(function() {
			var str = '';
			if (self.email()) {
				if (self.first_name() || self.last_name()) {
				str += self.first_name() + ' ' + self.last_name() + ' - ';
				}
				str += self.email();

			}			

			@if (Utils::isConfirmed())
			if (self.invitation_link()) {
				str += '<br/><a href="' + self.invitation_link() + '" target="_blank">{{ trans('texts.view_as_recipient') }}</a>';
			}
			@endif
			
			return str;
		});		
		
		if (data) {
			ko.mapping.fromJS(data, {}, this);		
		}		
	}

	function TaxRateModel(data) {
		var self = this;
		self.public_id = ko.observable('');
		self.rate = ko.observable(0);
		self.name = ko.observable('');
		self.is_deleted = ko.observable(false);
		self.is_blank = ko.observable(false);
		self.actionsVisible = ko.observable(false);

		if (data) {
			ko.mapping.fromJS(data, {}, this);		
		}		

		this.prettyRate = ko.computed({
	        read: function () {
	            return this.rate() ? parseFloat(this.rate()) : '';
	        },
	        write: function (value) {
	            this.rate(value);
	        },
	        owner: this
	    });				


		self.displayName = ko.computed({
			read: function () {
				var name = self.name() ? self.name() : '';
				var rate = self.rate() ? parseFloat(self.rate()) + '% ' : '';
				return rate + name;
			},
	        write: function (value) {
	        },
	        owner: this			
		});	

    	self.hideActions = function() {
			self.actionsVisible(false);
    	}

    	self.showActions = function() {
			self.actionsVisible(true);
    	}		

    	self.isEmpty = function() {
    		return !self.rate() && !self.name();
    	}    	
	}

	function ItemModel(data) {
		var self = this;		
		this.product_key = ko.observable('');
		this.notes = ko.observable('');
		this.cost = ko.observable(0);
		this.disc = ko.observable(0);
		this.qty = ko.observable(0);
		self.tax_name = ko.observable('');
		self.tax_rate = ko.observable(0);
		this.actionsVisible = ko.observable(false);
		
		self._tax = ko.observable();
		this.tax = ko.computed({
			read: function () {
				return self._tax();
			},
			write: function(value) {
				self._tax(value);								
				self.tax_name(value.name());
				self.tax_rate(value.rate());
			}
		})

		this.prettyQty = ko.computed({
	        read: function () {
	            return NINJA.parseFloat(this.qty()) ? NINJA.parseFloat(this.qty()) : '';
	        },
	        write: function (value) {
	            this.qty(value);
	        },
	        owner: this
	    });				

		this.prettyCost = ko.computed({
	        read: function () {
	            return this.cost() ? this.cost() : '';
	        },
	        write: function (value) {
	            this.cost(value);
	        },
	        owner: this
	    });	

	    this.prettyDisc = ko.computed({
	        read: function () {
	            return this.disc() ? this.disc() : '';
	        },
	        write: function (value) {
	            this.disc(value);
	        },
	        owner: this
	    });				

		self.mapping = {
		    'tax': {
		    	create: function(options) {
		    		return new TaxRateModel(options.data);
		    	}
		    }
		}

		if (data) {
			ko.mapping.fromJS(data, self.mapping, this);			
		}

		self.wrapped_notes = ko.computed({
			read: function() {
				return this.notes();
			},
			write: function(value) {
				value = wordWrapText(value, 250);
				self.notes(value);
				onItemChange();
			},
			owner: this
		});

		this.totals = ko.observable();

		this.totals.discTotal = ko.computed(function() {
			var cost = NINJA.parseFloat(self.cost());
			var qty = NINJA.parseFloat(self.qty());
			var disc = NINJA.parseFloat(self.disc());
    		var value = 0;  
	    	if (disc > 0) {
    			value = cost * qty * (disc/100);
	    	}    	 

  	  	return value ? roundToTwo(value) : '';
  	});


		this.totals.rawTotal = ko.computed(function() {
			var cost = NINJA.parseFloat(self.cost());
			var qty = NINJA.parseFloat(self.qty());
			var taxRate = NINJA.parseFloat(self.tax_rate());

    		var value = cost * qty;  
    	
    	if (taxRate > 0) {
    		value += value * (taxRate/100);
    	}    	
    	return value ? roundToTwo(value) : '';
  	});		

		this.totals.total = ko.computed(function() {
			var total = self.totals.rawTotal();
			if (window.hasOwnProperty('model') && model.invoice && model.invoice() && model.invoice().client()) {
				return total ? formatMoney(total, 1) : '';
			} else {
				return total ? formatMoney(total, 1) : '';
			}
  	});

  	this.hideActions = function() {
		this.actionsVisible(false);
  	}

  	this.showActions = function() {
		this.actionsVisible(true);
  	}

  	this.isEmpty = function() {
  		return !self.product_key() && !self.notes() && !self.cost() && (!self.qty());
  	}

  	this.onSelect = function(){              
    }
	}

	function onItemChange()
	{
		var hasEmpty = false;
		for(var i=0; i<model.invoice().invoice_items().length; i++) {
			var item = model.invoice().invoice_items()[i];
			if (item.isEmpty()) {
				hasEmpty = true;
			}
		}

		if (!hasEmpty) {
			model.invoice().addItem();
		}

		$('.word-wrap').each(function(index, input) {
			$(input).height($(input).val().split('\n').length * 20);
		});
	}


	var products = {{ $products }};
	var clients = {{ $clients }};	
	
	var clientMap = {};
	var $clientSelect = $('select#client');
	var invoiceDesigns = {{ $invoiceDesigns }};

	for (var i=0; i<clients.length; i++) {
		var client = clients[i];
		for (var j=0; j<client.contacts.length; j++) {
			var contact = client.contacts[j];
			if (contact.is_primary) {
				contact.send_invoice = true;
			}
		}
		clientMap[client.public_id] = client;
		$clientSelect.append(new Option(client.name, client.public_id));
	}

	@if ($data)
		window.model = new ViewModel({{ $data }});				
	@else 
		window.model = new ViewModel();
		model.addTaxRate();
		@foreach ($taxRates as $taxRate)
			model.addTaxRate({{ $taxRate }});
		@endforeach
		@if ($invoice)
			var invoice = {{ $invoice }};
			ko.mapping.fromJS(invoice, model.invoice().mapping, model.invoice);			
			if (model.invoice().is_recurring() === '0') {
				model.invoice().is_recurring(false);
			}
			var invitationContactIds = {{ json_encode($invitationContactIds) }};		
			var client = clientMap[invoice.client.public_id];
			if (client) { 
				for (var i=0; i<client.contacts.length; i++) {
					var contact = client.contacts[i];
					contact.send_invoice = invitationContactIds.indexOf(contact.public_id) >= 0;
				}			
			}
			model.invoice().addItem();			

		@endif
	@endif

	model.invoice().tax(model.getTaxRate(model.invoice().tax_name(), model.invoice().tax_rate()));			
	for (var i=0; i<model.invoice().invoice_items().length; i++) {
		var item = model.invoice().invoice_items()[i];
		item.tax(model.getTaxRate(item.tax_name(), item.tax_rate()));
		item.cost(NINJA.parseFloat(item.cost()) > 0 ? roundToTwo(item.cost(), true) : '');
	}

	if (!model.invoice().discount()) model.invoice().discount('');
	ko.applyBindings(model);	
	onItemChange();

	</script>

@stop
