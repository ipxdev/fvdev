@extends('header')

@section('content')
<div class="row">

	{{ Former::open($url)->addClass('col-md-12 warn-on-exit')->method($method)->rules(array(
  		'nit' => 'required|Numeric|min:5',		
  		'name' => 'required|min:3',
  		'business_name' => 'required|min:3',
  		'phone' => 'Numeric',
  		'work_phone' => 'Numeric',
  		'email' => 'email',
  		'address1' => 'min:4',
  		'address2' => 'min:4',
  		'private_notes' => 'min:4',
  		'first_name' => 'match:/[a-zA-Z. ]+/|min:3',
  		'last_name' => 'match:/[a-zA-Z. ]+/|min:3',
  		
	)); }}

	@if ($client)
		{{ Former::populate($client) }}
	@endif

	<div class="row">
		<div class="col-md-6">
			
			{{ Former::legend('organization') }}
			{{ Former::text('name')->label('Nombre (*)') }}     
			{{ Former::text('work_phone')->title('Solo se acepta Número Telefónico') }}

			@if ($customLabel2)
				{{ Former::text('custom_value2')->label($customLabel2) }}
			@endif
			@if ($customLabel3)
				{{ Former::text('custom_value3')->label($customLabel3) }}
			@endif
			@if ($customLabel1)
				{{ Former::text('custom_value1')->label($customLabel1) }}
			@endif
			@if ($customLabel4)
				{{ Former::text('custom_value4')->label($customLabel4) }}
			@endif
			@if ($customLabel5)
				{{ Former::text('custom_value5')->label($customLabel5) }}
			@endif
			@if ($customLabel6)
				{{ Former::text('custom_value6')->label($customLabel6) }}
			@endif
			@if ($customLabel7)
				{{ Former::text('custom_value7')->label($customLabel7) }}
			@endif
			@if ($customLabel8)
				{{ Former::text('custom_value8')->label($customLabel8) }}
			@endif
			
			{{ Former::legend('Datos para Facturar') }}

			{{ Former::text('business_name')->label('razón Social (*)') }}

			{{ Former::text('nit')->label('NIT/CI (*)') }}

			{{ Former::legend('address') }}
			{{ Former::text('address1') }}
			{{ Former::text('address2') }}


		</div>
		<div class="col-md-6">

			{{ Former::legend('contacts') }}
			<div data-bind='template: { foreach: contacts,
		                            beforeRemove: hideContact,
		                            afterAdd: showContact }'>
				{{ Former::hidden('public_id')->data_bind("value: public_id, valueUpdate: 'afterkeydown'") }}
				{{ Former::text('first_name')->data_bind("value: first_name, valueUpdate: 'afterkeydown'") }}
				{{ Former::text('last_name')->data_bind("value: last_name, valueUpdate: 'afterkeydown'") }}
				{{ Former::text('email')->data_bind('value: email, valueUpdate: \'afterkeydown\', attr: {id:\'email\'+$index()}') }}
				{{ Former::text('phone')->data_bind("value: phone, valueUpdate: 'afterkeydown'")->title('Solo se acepta Número Telefónico') }}	

				<div class="form-group">
					<div class="col-lg-8 col-lg-offset-4 bold">
						<span class="redlink bold" data-bind="visible: $parent.contacts().length > 1">
							{{ link_to('#', trans('texts.remove_contact').' -', array('data-bind'=>'click: $parent.removeContact')) }}
						</span>					
						<span data-bind="visible: $index() === ($parent.contacts().length - 1)" class="pull-right greenlink bold">
							{{ link_to('#', trans('texts.add_contact').' +', array('onclick'=>'return addContact()')) }}
						</span>
					</div>
				</div>
			</div>

			{{ Former::legend('additional_info') }}
				@if ($customLabel9)
					{{ Former::text('custom_value9')->label($customLabel9) }}
				@endif
				@if ($customLabel10)
					{{ Former::text('custom_value10')->label($customLabel10) }}
				@endif
				@if ($customLabel11)
					{{ Former::date('custom_value11')->label($customLabel11) }}
				@endif
				@if ($customLabel12)
					{{ Former::date('custom_value12')->label($customLabel12) }}
			@endif

			{{ Former::textarea('private_notes') }}

		</div>
	</div>

	{{ Former::hidden('data')->data_bind("value: ko.toJSON(model)") }}	

	<script type="text/javascript">

	$(function() {
		$('#country_id').combobox();
	});

	function ContactModel(data) {
		var self = this;
		self.public_id = ko.observable('');
		self.first_name = ko.observable('');
		self.last_name = ko.observable('');
		self.email = ko.observable('');
		self.phone = ko.observable('');

		if (data) {
			ko.mapping.fromJS(data, {}, this);			
		}		
	}

	function ContactsModel(data) {
		var self = this;
		self.contacts = ko.observableArray();;
		
		self.mapping = {
		    'contacts': {
		    	create: function(options) {
		    		return new ContactModel(options.data);
		    	}
		    }
		}		

		if (data) {
			ko.mapping.fromJS(data, self.mapping, this);			
		} else {
			self.contacts.push(new ContactModel());
		}
	}

	window.model = new ContactsModel({{ $client }});

	model.showContact = function(elem) { if (elem.nodeType === 1) $(elem).hide().slideDown() }
	model.hideContact = function(elem) { if (elem.nodeType === 1) $(elem).slideUp(function() { $(elem).remove(); }) }


	ko.applyBindings(model);

	function addContact() {
		model.contacts.push(new ContactModel());
		return false;
	}

	model.removeContact = function() {
		model.contacts.remove(this);
	}


	</script>

<hr>

	<center class="buttons">

    {{ Button::lg_default_link('clients/' . ($client ? $client->public_id : ''), trans('texts.cancel'))->append_with_icon('remove-circle'); }}
	
	{{ Button::lg_primary_submit_success(trans('texts.save'))->append_with_icon('floppy-disk') }}

	</center>

	{{ Former::close() }}
</div>
@stop
