@extends('header')

@section('content')

	<ul class="nav nav-tabs nav nav-justified">
    {{ HTML::nav_link('company/chart_builder', 'grafics') }}
    {{ HTML::nav_link('company/data_visualizations', 'data_visualizations') }}
	</ul>

	<br/>

@stop