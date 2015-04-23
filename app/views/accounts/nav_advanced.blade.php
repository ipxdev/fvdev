@extends('header')

@section('content')

<ul class="nav nav-tabs nav nav-justified">

  {{ HTML::nav_link('company/details', 'company_details') }}
  {{ HTML::nav_link('company/branches', 'branch_details') }}
  {{ HTML::nav_link('company/invoice_design', 'invoice_design') }}
  {{ HTML::nav_link('company/invoice_settings', 'invoice_settings') }}
  {{ HTML::nav_link('company/product_settings', 'product_settings') }}
  {{ HTML::nav_link('company/notifications', 'notifications') }}

  {{-- HTML::nav_link('company/advanced_settings/data_visualizations', 'data_visualizations') --}}
  {{-- HTML::nav_link('company/advanced_settings/chart_builder', 'chart_builder') --}}

</ul>
<p>&nbsp;</p>

@stop