@extends('header')

@section('content')


<div class="row">
    <div class="panel-heading" style="margin:0;">
      <h3 class="panel-title in-bold">
       {{ trans('texts.general') }}
      </h3>
    </div>
</div>
      <table class="table" style="width:100%">
        <tr>
          <td>
<div class="row">
  <div class="col-md-4">  
    <div class="panel panel-default">
      <div class="panel-body">
        <img src="{{ asset('images/totalincome.png') }}" class="in-image"/>  
        <div class="in-bold">
          {{ $totalIncome }}
        </div>
        <div class="in-thin">
          {{ trans('texts.in_total_revenue') }}
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-body">
        <img src="{{ asset('images/clients.png') }}" class="in-image"/>  
        <div class="in-bold">
          {{ $billedClients }}
        </div>
        <div class="in-thin">
          {{ Utils::pluralize('billed_client', $billedClients) }}
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="panel panel-default">
      <div class="panel-body">
        <img src="{{ asset('images/totalinvoices.png') }}" class="in-image"/>  
        <div class="in-bold">
          {{ $invoicesSent }}
        </div>
        <div class="in-thin">
          de {{ Utils::pluralize('invoice', $invoicesSent) }} emitidas
        </div>
      </div>
    </div>
  </div>
</div>
            </td>       
        </tr>
      </table>

<p>&nbsp;</p>

<div class="row">
  <div class="col-md-6">  
    <div class="panel panel-default dashboard" style="min-height:655px">
      <div class="panel-heading" style="background-color:#0b4d78">
        <h3 class="panel-title in-bold-white">
          <i class="glyphicon glyphicon-exclamation-sign"></i> {{ trans('texts.notifications') }}
        </h3>
      </div>
      <ul class="panel-body list-group">
      @foreach ($activities as $activity)
        <li class="list-group-item">
          <span style="color:#888;font-style:italic">{{ Utils::timestampToDateString(strtotime($activity->created_at)) }}:</span>
          {{ Utils::decodeActivity($activity->message) }}
        </li>
      @endforeach
      </ul>
    </div>  
  </div>
  <div class="col-md-6">  
    <div class="panel panel-default dashboard" style="min-height:320px">
      <div class="panel-heading" style="background-color:#e37329">
        <h3 class="panel-title in-bold-white">
          <i class="glyphicon glyphicon-time"></i> {{ trans('texts.invoices_past_due') }}
        </h3>
      </div>
      <div class="panel-body">
        <table class="table table-striped">
          <thead>
            <th>{{ trans('texts.invoice_number_short') }}</th>
            <th>{{ trans('texts.client') }}</th>
            <th>{{ trans('texts.due_date') }}</th>
            <th>{{ trans('texts.balance_due') }}</th>
          </thead>
          <tbody>
            @foreach ($pastDue as $invoice)
              <tr>
                <td>{{ $invoice->getLink() }}</td>
                <td>{{ $invoice->client->getDisplayName() }}</td>
                <td>{{ Utils::fromSqlDate($invoice->due_date) }}</td>
                <td>{{ Utils::formatMoney($invoice->balance, $invoice->client->currency_id) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>  
  </div>

  <div class="col-md-6">  
    <div class="panel panel-default dashboard" style="min-height:320px;">
      <div class="panel-heading" style="background-color:#36C157">
        <h3 class="panel-title in-bold-white">
          <i class="glyphicon glyphicon-time"></i> {{ trans('texts.upcoming_invoices') }}
        </h3>
      </div>
      <div class="panel-body">
        <table class="table table-striped">
          <thead>
            <th>{{ trans('texts.invoice_number_short') }}</th>
            <th>{{ trans('texts.client') }}</th>
            <th>{{ trans('texts.due_date') }}</th>
            <th>{{ trans('texts.balance_due') }}</th>
          </thead>
          <tbody>
            @foreach ($upcoming as $invoice)
              <tr>
                <td>{{ $invoice->getLink() }}</td>
                <td>{{ $invoice->client->getDisplayName() }}</td>
                <td>{{ Utils::fromSqlDate($invoice->due_date) }}</td>
                <td>{{ Utils::formatMoney($invoice->balance, $invoice->client->currency_id) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<div class="row">

  <div class="col-md-3">
    <div class="active-clients">      
      <div class="in-bold in-white" style="font-size:30px">{{ $activeClients }}</div>
      <div class="in-thin in-white">{{ Utils::pluralize('active_client', $activeClients) }}</div>
    </div>
      </div>

    <div class="col-md-3">
    <div class="average-products">      
      <div class="in-bold in-white" style="font-size:30px">{{ $activeProducts }}</div>
      <div class="in-thin in-white">Total de Productos</div>
    </div>
      </div>
  <div class="col-md-3">
    <div class="average-anual">  
      <div><b>Ingreso total anual</b></div>
      <div class="in-bold in-white" style="font-size:30px">{{ $totalIncomeY }}</div>
    </div>      
  </div>
  <div class="col-md-3">
    <div class="average-invoice">  
      <div><b>{{ trans('texts.average_invoice') }}</b></div>
      <div class="in-bold in-white" style="font-size:30px">{{ $invoiceAvg }}</div>
    </div>      
  </div>

</div>
@stop