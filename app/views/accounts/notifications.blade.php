@extends('accounts.nav_advanced')
	

@section('content')	
{{ Former::legend('panel_settings') }}
	@parent

	{{ Former::open()->addClass('col-md-8 col-md-offset-2 warn-on-exit') }}	
	{{ Former::populate($account) }}
	{{ Former::populateField('notify_sent', intval(Auth::user()->notify_sent)) }}
	{{ Former::populateField('notify_viewed', intval(Auth::user()->notify_viewed)) }}
	{{ Former::populateField('notify_paid', intval(Auth::user()->notify_paid)) }}

	{{ Former::legend('email_notifications') }}
	{{ Former::checkbox('notify_sent')->label('&nbsp;')->text(trans('texts.email_sent')) }}
	{{ Former::checkbox('notify_viewed')->label('&nbsp;')->text(trans('texts.email_viewed')) }}
	{{ Former::checkbox('notify_paid')->label('&nbsp;')->text(trans('texts.email_paid')) }}

	{{ Former::legend('custom_messages') }}
	{{ Former::textarea('invoice_terms')->label(trans('texts.default_invoice_terms')) }}
	{{ Former::textarea('email_footer')->label(trans('texts.default_email_footer')) }} 

	{{ Former::actions( Button::lg_success_submit(trans('texts.save'))->append_with_icon('floppy-disk') ) }}
	{{ Former::close() }}

@stop