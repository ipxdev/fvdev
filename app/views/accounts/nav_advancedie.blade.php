@extends('header')

@section('content')

<ul class="nav nav-tabs nav nav-justified">

  {{ HTML::nav_link('company/import_export', 'import_inv') }}
  {{ HTML::nav_link('company/export_book', 'export_book') }}
  {{ HTML::nav_link('company/import_exportc', 'import_cli') }}



</ul>
<p>&nbsp;</p>

@stop