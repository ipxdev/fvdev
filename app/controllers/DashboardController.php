<?php
class DashboardController extends \BaseController {
  public function index()
  {
    
    $select = DB::raw('COUNT(DISTINCT CASE WHEN invoices.id IS NOT NULL THEN clients.id ELSE null END) billed_clients,
                        SUM(CASE WHEN invoices.invoice_status_id >= '.INVOICE_STATUS_DRAFT.' THEN 1 ELSE 0 END) invoices_sent,
                        AVG(invoices.amount) as invoice_avg');

    $metrics = DB::table('accounts')
            ->select($select)
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('clients.is_deleted', '=', false)
            ->groupBy('accounts.id')
            ->first();

    $selectC = DB::raw('COUNT(DISTINCT clients.id) active_clients');
    
    $metrics2 = DB::table('accounts')
            ->select($selectC)
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('clients.deleted_at', '=', null)
            ->groupBy('accounts.id')
            ->first();
     
    $selectP = DB::raw('COUNT(DISTINCT products.id) active_products');

    $metrics3 = DB::table('accounts')
            ->select($selectP)
            ->leftJoin('products', 'accounts.id', '=', 'products.account_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('products.deleted_at', '=', null)
            ->groupBy('accounts.id')
            ->first();

    $select = DB::raw('SUM(clients.paid_to_date) as value');

    $totalIncome1 = DB::table('accounts')
            ->select($select)
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('clients.is_deleted', '=', false)
            ->groupBy('accounts.id')
            ->first();

    $totalIncomeT = DB::table('accounts')
            ->select($select)
            ->leftJoin('clients', 'accounts.id', '=', 'clients.account_id')
            ->leftJoin('invoices', 'clients.id', '=', 'invoices.client_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('clients.deleted_at', '=', null)
            ->groupBy('accounts.id')
            ->first();

    $select = DB::raw('SUM(payments.amount) as value');

    $totalIncome = DB::table('accounts')
            ->select($select)
            ->leftJoin('payments', 'accounts.id', '=', 'payments.account_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('payments.payment_date', '>=', Carbon::now()->startOfMonth())
            ->groupBy('accounts.id')
            ->first();

    $select = DB::raw('SUM(payments.amount) as value');

    $totalIncomeY = DB::table('accounts')
            ->select($select)
            ->leftJoin('payments', 'accounts.id', '=', 'payments.account_id')
            ->where('accounts.id', '=', Auth::user()->account_id)
            ->where('payments.payment_date', '>=', Carbon::now()->startOfYear())
            ->groupBy('accounts.id')
            ->first();

    $activities = Activity::where('activities.account_id', '=', Auth::user()->account_id)
                ->orderBy('created_at', 'desc')->take(15)->get();

    $pastDue = Invoice::scope()
                ->where('due_date', '<', date('Y-m-d'))
                ->where('balance', '>', 0)
                ->where('is_recurring', '=', false)
                ->where('is_quote', '=', false)
                ->where('is_deleted', '=', false)
                ->orderBy('due_date', 'asc')->take(6)->get();

    $upcoming = Invoice::scope()
                  ->where('due_date', '>', date('Y-m-d'))
                  ->where('balance', '>', 0)
                  ->where('is_recurring', '=', false)
                  ->where('is_quote', '=', false)
                  ->where('is_deleted', '=', false)
                  ->orderBy('due_date', 'asc')->take(6)->get();
    $data = [
      'totalIncome' => Utils::formatMoney($totalIncome ? $totalIncome->value : 0, Session::get(SESSION_CURRENCY)),
      'totalIncomeY' => Utils::formatMoney($totalIncomeY ? $totalIncomeY->value : 0, Session::get(SESSION_CURRENCY)),
      'billedClients' => $metrics ? $metrics->billed_clients : 0,
      'invoicesSent' => $metrics ? $metrics->invoices_sent : 0,
      'activeClients' => $metrics2 ? $metrics2->active_clients : 0,
      'activeProducts' => $metrics3 ? $metrics3->active_products : 0,
      'invoiceAvg' => Utils::formatMoney(($metrics ? $metrics->invoice_avg : 0), Session::get(SESSION_CURRENCY)),
      'activities' => $activities,
      'pastDue' => $pastDue,
      'upcoming' => $upcoming
    ];
    return View::make('dashboard', $data);
  }
}