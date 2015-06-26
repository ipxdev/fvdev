<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('install', 'AccountController@install');
Route::get('update', 'AccountController@update');
Route::get('reset', 'AccountController@reset');

Route::get('/', 'HomeController@showIndex');
Route::post('/contact_submit', 'HomeController@doContactUs');

Route::get('log_error', 'HomeController@logError');
Route::get('crear', 'HomeController@invoiceNow');
Route::post('get_started', 'AccountController@getStarted');

Route::get('view/{invitation_key}', 'InvoiceController@view');
Route::get('payment/{invitation_key}', 'PaymentController@show_payment');
Route::post('payment/{invitation_key}', 'PaymentController@do_payment');
Route::get('complete', 'PaymentController@offsite_payment');

Route::get('license', 'PaymentController@show_license_payment');
Route::post('license', 'PaymentController@do_license_payment');
Route::get('claim_license', 'PaymentController@claim_license');

Route::post('signup/validate', 'AccountController@checkEmail');
Route::post('signup/submit', 'AccountController@submitSignup');

// Confide routes
Route::get('login', 'UserController@login');
Route::post('login', 'UserController@do_login');
Route::get('user/confirm/{code}', 'UserController@confirm');
Route::get('forgot_password', 'UserController@forgot_password');
Route::post('forgot_password', 'UserController@do_forgot_password');
Route::get('user/reset/{token?}', 'UserController@reset_password');
Route::post('user/reset', 'UserController@do_reset_password');
Route::get('logout', 'UserController@logout');

// API POS
Route::group(array('before' => 'auth.basic'), function()
{
  Route::post('saveclient','ClientController@saveclient');

  Route::get('facturas','InvoiceController@facturas');
  Route::get('factura/{numeroFactura}','InvoiceController@factura');
  Route::get('printFactura/{numeroFactura}','InvoiceController@printFactura');
  Route::post('guardarFactura','InvoiceController@guardarFactura');
  Route::post('guardarFacturaG','InvoiceController@guardarFacturaG');
  Route::get('loginPOS','InvoiceController@listasCuenta');
  Route::get('cliente/{nit}','ClientController@cliente');
  Route::post('mensajeInvoice','InvoiceController@mensajeInvoice');
 
  Route::get('obtenerFactura/{public_id}','ClientController@obtenerFactura');

  Route::get('msg','ClientController@mensaje');
  Route::get('mensajeCliente','ClientController@mensajeCliente');
  Route::post('mensajeInvoice','InvoiceController@mensajeInvoice');

});

Route::group(array('before' => 'auth'), function()
{   
  Route::get('select_branch', 'UserController@select_branch');
  Route::post('select_branch', 'UserController@do_select_branch');
  Route::get('dashboard', 'DashboardController@index');
  Route::get('view_archive/{entity_type}/{visible}', 'AccountController@setTrashVisible');
  Route::get('hide_message', 'HomeController@hideMessage');
  Route::get('force_inline_pdf', 'UserController@forcePDFJS');

  Route::get('api/users', array('as'=>'api.users', 'uses'=>'UserController@getDatatable'));
  Route::resource('users', 'UserController');
  Route::post('users/delete', 'UserController@delete');

  Route::resource('products', 'ProductController');
  Route::get('api/products', array('as'=>'api.products', 'uses'=>'ProductController@getDatatable'));
  Route::get('api/activities/{product_id?}', array('as'=>'api.activities', 'uses'=>'ActivityController@getDatatable'));  
  Route::get('products/bulk', 'ProductController@bulk');
  Route::get('products/{product_id}/archive', 'ProductController@archive');

  Route::get('api/branches', array('as'=>'api.branches', 'uses'=>'BranchController@getDatatable'));
  Route::resource('branches', 'BranchController');
  Route::get('branches/{branch_id}/archive', 'BranchController@archive');

  Route::get('api/categories', array('as'=>'api.categories', 'uses'=>'CategoryController@getDatatable'));
  Route::resource('categories', 'CategoryController');
  Route::get('categories/{category_id}/archive', 'CategoryController@archive');

  Route::get('api/manuals', array('as'=>'api.manuals', 'uses'=>'ManualController@getDatatable'));
  Route::resource('manuals', 'ManualController');
  Route::get('manuals/{manual_id}/archive', 'ManualController@archive');

  Route::get('company/data_visualizations', 'ReportController@d3');
  Route::get('company/chart_builder', 'ReportController@report');
  Route::post('company/chart_builder', 'ReportController@report');

	Route::get('account/getSearchData', array('as' => 'getSearchData', 'uses' => 'AccountController@getSearchData'));
  Route::get('company/{section?}/{sub_section?}', 'AccountController@showSection');	
	Route::post('company/{section?}/{sub_section?}', 'AccountController@doSection');
	Route::post('user/setTheme', 'UserController@setTheme');
  Route::post('remove_logo', 'AccountController@removeLogo');
  Route::post('account/go', 'AccountController@enablePlan');
  Route::post('account/go_pro', 'AccountController@enableProPlan');
  Route::post('account/go_pro2', 'AccountController@enableProPlan2');


	Route::resource('clients', 'ClientController');
	Route::get('api/clients', array('as'=>'api.clients', 'uses'=>'ClientController@getDatatable'));
	Route::get('api/activities/{client_id?}', array('as'=>'api.activities', 'uses'=>'ActivityController@getDatatable'));	
	Route::post('clients/bulk', 'ClientController@bulk');

	Route::get('recurring_invoices', 'InvoiceController@recurringIndex');
	Route::get('api/recurring_invoices/{client_id?}', array('as'=>'api.recurring_invoices', 'uses'=>'InvoiceController@getRecurringDatatable'));	

  Route::resource('invoices', 'InvoiceController');
  Route::get('api/invoices/{client_id?}', array('as'=>'api.invoices', 'uses'=>'InvoiceController@getDatatable')); 
  Route::get('invoices/create/{client_id?}', 'InvoiceController@create');
  Route::get('invoices/{public_id}/clone', 'InvoiceController@cloneInvoice');
  Route::post('invoices/bulk', 'InvoiceController@bulk');

  Route::get('quotes/create/{client_id?}', 'QuoteController@create');
  Route::get('quotes/{public_id}/clone', 'InvoiceController@cloneInvoice');
  Route::get('quotes/{public_id}/edit', 'InvoiceController@edit');
  Route::put('quotes/{public_id}', 'InvoiceController@update');
  Route::get('quotes/{public_id}', 'InvoiceController@edit');
  Route::post('quotes', 'InvoiceController@store');
  Route::get('quotes', 'QuoteController@index');
  Route::get('api/quotes/{client_id?}', array('as'=>'api.quotes', 'uses'=>'QuoteController@getDatatable'));   
  Route::post('quotes/bulk', 'QuoteController@bulk');

	Route::get('payments/{id}/edit', function() { return View::make('header'); });
	Route::resource('payments', 'PaymentController');
	Route::get('payments/create/{client_id?}/{invoice_id?}', 'PaymentController@create');
	Route::get('api/payments/{client_id?}', array('as'=>'api.payments', 'uses'=>'PaymentController@getDatatable'));
	Route::post('payments/bulk', 'PaymentController@bulk');
	
	Route::get('credits/{id}/edit', function() { return View::make('header'); });
	Route::resource('credits', 'CreditController');
	Route::get('credits/create/{client_id?}/{invoice_id?}', 'CreditController@create');
	Route::get('api/credits/{client_id?}', array('as'=>'api.credits', 'uses'=>'CreditController@getDatatable'));	
	Route::post('credits/bulk', 'CreditController@bulk');	
    
});

// Route group for API
Route::group(array('prefix' => 'api/v1', 'before' => 'auth.basic'), function()
{
  Route::resource('ping', 'ClientApiController@ping');
  Route::resource('clients', 'ClientApiController');
  Route::resource('invoices', 'InvoiceApiController');
  Route::resource('quotes', 'QuoteApiController');
  Route::resource('payments', 'PaymentApiController');
  Route::post('api/hooks', 'IntegrationController@subscribe');
});

define('CONTACT_EMAIL', 'servicio@facturavirtual.com.bo');
define('CONTACT_NAME', 'Facturación Virtual');
define('SITE_URL', 'https://cloud.facturavirtual.com.bo');

define('ENV_DEVELOPMENT', 'local');
define('ENV_STAGING', 'staging');
define('ENV_PRODUCTION', 'fortrabbit');

define('RECENTLY_VIEWED', 'RECENTLY_VIEWED');
define('ENTITY_CLIENT', 'client');
define('ENTITY_PRODUCT', 'product');
define('ENTITY_INVOICE', 'invoice');
define('ENTITY_RECURRING_INVOICE', 'recurring_invoice');
define('ENTITY_PAYMENT', 'payment');
define('ENTITY_CREDIT', 'credit');
define('ENTITY_QUOTE', 'quote');

define('PERSON_CONTACT', 'contact');
define('PERSON_USER', 'user');

define('ACCOUNT_DETAILS', 'details');
define('ACCOUNT_BRANCHES', 'branches');
define('ACCOUNT_USERS', 'user_management');
define('ACCOUNT_NOTIFICATIONS', 'notifications');
define('ACCOUNT_IMPORT_EXPORT', 'import_export');
define('ACCOUNT_EXPORT_BOOK', 'export_book');
define('ACCOUNT_IMPORT_EXPORTC', 'import_exportc');
define('ACCOUNT_IMPORT_EXPORTI', 'import_exporti');
define('ACCOUNT_PAYMENTS', 'payments');
define('ACCOUNT_MAP', 'import_map');
define('ACCOUNT_MAP_INVOICES', 'import_map_invoice');
define('ACCOUNT_EXPORT', 'export');
define('ACCOUNT_EXPORTBOOKSALE', 'booksale');
define('ACCOUNT_CATEGORIES', 'categories');
define('ACCOUNT_PRODUCTS', 'products');
define('ACCOUNT_MANUALS', 'manuals');
define('ACCOUNT_ADVANCED_SETTINGS', 'advanced_settings');
define('ACCOUNT_INVOICE_SETTINGS', 'invoice_settings');
define('ACCOUNT_PRODUCT_SETTINGS', 'product_settings');
define('ACCOUNT_INVOICE_DESIGN', 'invoice_design');
define('ACCOUNT_CHART_BUILDER', 'chart_builder');
define('ACCOUNT_DATA_VISUALIZATIONS', 'data_visualizations');

define('DEFAULT_INVOICE_NUMBER', '0001');
define('RECENTLY_VIEWED_LIMIT', 8);
define('LOGGED_ERROR_LIMIT', 100);
define('RANDOM_KEY_LENGTH', 32);
define('MAX_NUM_CLIENTS', 500);
define('MAX_NUM_CLIENTS_PRO', 20000);
define('MAX_NUM_USERS', 5);

define('INVOICE_STATUS_DRAFT', 1);
define('INVOICE_STATUS_SENT', 2);
define('INVOICE_STATUS_VIEWED', 3);
define('INVOICE_STATUS_PARTIAL', 4);
define('INVOICE_STATUS_PAID', 5);

define('PAYMENT_TYPE_CREDIT', 2);

define('FREQUENCY_WEEKLY', 1);
define('FREQUENCY_TWO_WEEKS', 2);
define('FREQUENCY_FOUR_WEEKS', 3);
define('FREQUENCY_MONTHLY', 4);
define('FREQUENCY_THREE_MONTHS', 5);
define('FREQUENCY_SIX_MONTHS', 6);
define('FREQUENCY_ANNUALLY', 7);

define('SESSION_TIMEZONE', 'timezone');
define('SESSION_CURRENCY', 'currency');
define('SESSION_DATE_FORMAT', 'dateFormat');
define('SESSION_DATE_PICKER_FORMAT', 'datePickerFormat');
define('SESSION_DATETIME_FORMAT', 'datetimeFormat');
define('SESSION_COUNTER', 'sessionCounter');
define('SESSION_LOCALE', 'sessionLocale');

define('DEFAULT_TIMEZONE', 'America/La_Paz');
define('DEFAULT_CURRENCY', 1);
define('DEFAULT_DATE_FORMAT', 'M j, Y');
define('DEFAULT_DATE_PICKER_FORMAT', 'M d, yyyy');
define('DEFAULT_DATETIME_FORMAT', 'F j, Y, g:i a');
define('DEFAULT_QUERY_CACHE', 120); // minutes
define('DEFAULT_LOCALE', 'es');

define('RESULT_SUCCESS', 'success');
define('RESULT_FAILURE', 'failure');


define('PAYMENT_LIBRARY_OMNIPAY', 1);
define('PAYMENT_LIBRARY_PHP_PAYMENTS', 2);

define('GATEWAY_AUTHORIZE_NET', 1);
define('GATEWAY_AUTHORIZE_NET_SIM', 2);
define('GATEWAY_PAYPAL_EXPRESS', 17);
define('GATEWAY_STRIPE', 23);
define('GATEWAY_TWO_CHECKOUT', 27);
define('GATEWAY_BEANSTREAM', 29);
define('GATEWAY_PSIGATE', 30);
define('GATEWAY_MOOLAH', 31);

define('EVENT_CREATE_CLIENT', 1);
define('EVENT_CREATE_INVOICE', 2);
define('EVENT_CREATE_QUOTE', 3);
define('EVENT_CREATE_PAYMENT', 4);

define('REQUESTED_PRO_PLAN', 'REQUESTED_PRO_PLAN');
define('DEMO_ACCOUNT_ID', 'DEMO_ACCOUNT_ID');
define('IPX_ACCOUNT_KEY', 'nGN0MGAljj16ANu5EE7x7VwoDJEg3Gxu');
define('NINJA_GATEWAY_ID', GATEWAY_AUTHORIZE_NET);
define('NINJA_GATEWAY_CONFIG', '{"apiLoginId":"626vWcD5","transactionKey":"4bn26TgL9r4Br4qJ","testMode":"","developerMode":""}');
define('NINJA_URL', 'https://cloud.facturavirtual.com.bo');
define('NINJA_VERSION', '5.0');

define('COUNT_FREE_DESIGNS', 4);
define('PRO_PLAN_PRICE', 300);
define('PRODUCT_ONE_CLICK_INSTALL', 1);
define('PRODUCT_INVOICE_DESIGNS', 2);
define('DESIGNS_AFFILIATE_KEY', 'T3RS74');

define('USER_TYPE_SELF_HOST', 'SELF_HOST');
define('USER_TYPE_CLOUD_HOST', 'CLOUD_HOST');
define('NEW_VERSION_AVAILABLE', 'NEW_VERSION_AVAILABLE');

/*
define('GATEWAY_AMAZON', 30);
define('GATEWAY_BLUEPAY', 31);
define('GATEWAY_BRAINTREE', 32);
define('GATEWAY_GOOGLE', 33);
define('GATEWAY_QUICKBOOKS', 35);
*/

/** 
 * TEST VALUES FOR THE CREDIT CARDS
 * NUMBER IS FOR THE BINARY COUNT FOR WHICH IMAGES TO DISPLAY  
 * card IS FOR CARD IMAGE AND text IS FOR CARD NAME (TO ADD TO alt FOR IMAGE) 
**/
$creditCards = [
            1 => ['card' => 'images/credit_cards/Test-Visa-Icon.png', 'text' => 'Visa'],
            2 => ['card' => 'images/credit_cards/Test-MasterCard-Icon.png', 'text' => 'Master Card'],
            4 => ['card' => 'images/credit_cards/Test-AmericanExpress-Icon.png', 'text' => 'American Express'],
            8 => ['card' => 'images/credit_cards/Test-Diners-Icon.png', 'text' => 'Diners'],
            16 => ['card' => 'images/credit_cards/Test-Discover-Icon.png', 'text' => 'Discover']
        ];
					
define('CREDIT_CARDS', serialize($creditCards));


HTML::macro('nav_link', function($url, $text, $url2 = '', $extra = '') {
    $class = ( Request::is($url) || Request::is($url.'/*') || Request::is($url2) ) ? ' class="active"' : '';
    $title = ucwords(trans("texts.$text")) . Utils::getProLabel($text);
    return '<li'.$class.'><a href="'.URL::to($url).'" '.$extra.'>'.$title.'</a></li>';
});

HTML::macro('tab_link', function($url, $text, $active = false) {
    $class = $active ? ' class="active"' : '';
    return '<li'.$class.'><a href="'.URL::to($url).'" data-toggle="tab">'.$text.'</a></li>';
});

HTML::macro('menu_link', function($type) {
  $types = $type.'s';
  $Type = ucfirst($type);
  $Types = ucfirst($types);
  $class = ( Request::is($types) || Request::is('*'.$type.'*')) && !Request::is('*advanced_settings*') ? ' active' : '';

  return '<li class="dropdown '.$class.'">
           <a href="'.URL::to($types).'" class="dropdown-toggle">'.trans("texts.$types").'</a>
           <ul class="dropdown-menu" id="menu1">
             <li><a href="'.URL::to($types.'/create').'">'.trans("texts.new_$type").'</a></li>
            </ul>
          </li>';
});

HTML::macro('menu_linkProduct', function($type) {
  $types = $type.'s';
  $Type = ucfirst($type);
  $Types = ucfirst($types);
  $class = ( Request::is($types) || Request::is('*'.$type.'*')) && !Request::is('*advanced_settings*') ? ' active' : '';

  return '<li class="dropdown '.$class.'">
           <a href="'.URL::to($types).'" class="dropdown-toggle">'.trans("texts.$types").'</a>
           <ul class="dropdown-menu" id="menu1">
             <li><a href="'.URL::to($types.'/create').'">'.trans("texts.new_$type").'</a></li>
             <li><a href="'.URL::to('/company/categories').'">Categorias</a></li>
            </ul>
          </li>';
});

HTML::macro('image_data', function($imagePath) {
  return 'data:image/jpeg;base64,' . base64_encode(file_get_contents(public_path().'/'.$imagePath));
});


HTML::macro('breadcrumbs', function() {
  $str = '<ol class="breadcrumb">';

  // Get the breadcrumbs by exploding the current path.
  $basePath = Utils::basePath();
  $parts = explode('?', $_SERVER['REQUEST_URI']);
  $path = $parts[0];
  
  if ($basePath != '/')
  {
    $path = str_replace($basePath, '', $path);
  }
  $crumbs = explode('/', $path);

  foreach ($crumbs as $key => $val)
  {
    if (is_numeric($val))
    {
      unset($crumbs[$key]);
    }
  }

  $crumbs = array_values($crumbs);
  for ($i=0; $i<count($crumbs); $i++) {
    $crumb = trim($crumbs[$i]);
    if (!$crumb) continue;
    if ($crumb == 'company') return '';
    $name = trans("texts.$crumb");
    if ($i==count($crumbs)-1) 
    {
      $str .= "<li class='active'>$name</li>";  
    }
    else
    {
      $str .= '<li>'.link_to($crumb, $name).'</li>';   
    }
  }
  return $str . '</ol>';
});

function uctrans($text)
{
  return ucwords(trans($text));
}


if (Auth::check() && !Session::has(SESSION_TIMEZONE)) 
{
	Event::fire('user.refresh');
}

Validator::extend('positive', function($attribute, $value, $parameters)
{
    return Utils::parseFloat($value) > 0;
});

Validator::extend('has_credit', function($attribute, $value, $parameters)
{
	$publicClientId = $parameters[0];
	$amount = $parameters[1];
	
	$client = Client::scope($publicClientId)->firstOrFail();
	$credit = $client->getTotalCredit();
  
  return $credit >= $amount;
});


