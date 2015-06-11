<?php
use Illuminate\Database\Migrations\Migration;

class ConfideSetupUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::dropIfExists('payment_terms');             
        Schema::dropIfExists('themes');        
        Schema::dropIfExists('credits');        
        Schema::dropIfExists('activities');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('account_gateways');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tax_rates');        
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('password_reminders');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('currencies');        
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('timezones');        
        Schema::dropIfExists('frequencies');        
        Schema::dropIfExists('date_formats');        
        Schema::dropIfExists('datetime_formats');                
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('gateways');
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('book_sales');
        Schema::dropIfExists('book_purchases');
        Schema::dropIfExists('manuals');

        Schema::create('countries', function($t)
        {           
            $t->increments('id');
            $t->string('capital', 255)->nullable();
            $t->string('citizenship', 255)->nullable();
            $t->string('country_code', 3)->default('');
            $t->string('currency', 255)->nullable();
            $t->string('currency_code', 255)->nullable();
            $t->string('currency_sub_unit', 255)->nullable();
            $t->string('full_name', 255)->nullable();
            $t->string('iso_3166_2', 2)->default('');
            $t->string('iso_3166_3', 3)->default('');
            $t->string('name', 255)->default('');
            $t->string('region_code', 3)->default('');
            $t->string('sub_region_code', 3)->default('');
            $t->boolean('eea')->default(0);                        
        });

        Schema::create('themes', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('payment_types', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('payment_terms', function($t)
        {
            $t->increments('id');
            $t->integer('num_days');
            $t->string('name');
        });

        Schema::create('timezones', function($t)
        {
            $t->increments('id');
            $t->string('name');
            $t->string('location');
        });

        Schema::create('date_formats', function($t)
        {
            $t->increments('id');
            $t->string('format');    
            $t->string('picker_format');                    
            $t->string('label');            
        });

        Schema::create('datetime_formats', function($t)
        {
            $t->increments('id');
            $t->string('format');            
            $t->string('label');            
        });

        Schema::create('currencies', function($t)
        {
            $t->increments('id');            

            $t->string('name');
            $t->string('symbol');
            $t->string('precision');
            $t->string('thousand_separator');
            $t->string('decimal_separator');
            $t->string('code');
        });  

        Schema::create('sizes', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });        

        Schema::create('industries', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });    


        Schema::create('accounts', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('timezone_id')->nullable();
            $t->unsignedInteger('date_format_id')->nullable();
            $t->unsignedInteger('datetime_format_id')->nullable();
            $t->unsignedInteger('currency_id')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->string('nit');
            $t->string('name')->nullable();
            $t->string('ip');
            $t->string('account_key')->unique();
            $t->timestamp('last_login')->nullable();

            $t->integer('credit_counter')->nullable();
            
            $t->string('address1')->nullable();
            $t->string('address2')->nullable();
            $t->string('city')->nullable();
            $t->string('state')->nullable();
            $t->string('postal_code')->nullable();
            $t->unsignedInteger('country_id')->nullable();     
            $t->text('invoice_terms')->nullable();
            $t->text('email_footer')->nullable();
            $t->unsignedInteger('industry_id')->nullable();
            $t->unsignedInteger('size_id')->nullable();

            $t->string('work_phone')->nullable();
            $t->string('work_email')->nullable();

            $t->date('pro_plan_paid')->null();

            $t->boolean('cod_search')->default(false);
            
            $t->string('aux1')->nullable();
            $t->string('aux2')->nullable();

            $t->string('custom_label1')->nullable();
            $t->string('custom_value1')->nullable();

            $t->string('custom_label2')->nullable();
            $t->string('custom_value2')->nullable();

            $t->string('custom_client_label1')->nullable();         
            $t->string('custom_client_label2')->nullable();
            $t->string('custom_client_label3')->nullable();
            $t->string('custom_client_label4')->nullable();
            $t->string('custom_client_label5')->nullable();
            $t->string('custom_client_label6')->nullable();
            $t->string('custom_client_label7')->nullable();
            $t->string('custom_client_label8')->nullable();
            $t->string('custom_client_label9')->nullable();
            $t->string('custom_client_label10')->nullable();
            $t->string('custom_client_label11')->nullable();
            $t->string('custom_client_label12')->nullable();

            $t->boolean('hide_quantity')->default(0);
            $t->boolean('hide_paid_to_date')->default(0);

            $t->string('custom_invoice_label1')->nullable();
            $t->string('custom_invoice_label2')->nullable();            

            $t->boolean('custom_invoice_taxes1')->nullable();
            $t->boolean('custom_invoice_taxes2')->nullable();

            $t->string('vat_number')->nullable();

            $t->boolean('invoice_taxes')->default(true);
            $t->boolean('invoice_item_taxes')->default(false);

            $t->boolean('fill_products')->default(true);
            $t->boolean('update_products')->default(true);

            $t->foreign('timezone_id')->references('id')->on('timezones');
            $t->foreign('date_format_id')->references('id')->on('date_formats');
            $t->foreign('datetime_format_id')->references('id')->on('datetime_formats');
            $t->foreign('country_id')->references('id')->on('countries');
            $t->foreign('currency_id')->references('id')->on('currencies');
            $t->foreign('industry_id')->references('id')->on('industries');
            $t->foreign('size_id')->references('id')->on('sizes');

        });     
        
        Schema::create('gateways', function($t)
        {
            $t->increments('id');
            $t->timestamps();            

            $t->string('name');
            $t->string('provider');
            $t->boolean('visible')->default(true);
        });  

        Schema::create('branches', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            
            $t->timestamps();
            $t->softDeletes();
            
            $t->string('name');
            $t->string('address1')->nullable();
            $t->string('address2')->nullable();
            $t->string('city')->nullable();
            $t->string('state')->nullable();
            $t->string('postal_code')->nullable();

            $t->unsignedInteger('country_id')->nullable();     
            $t->text('invoice_terms');
            $t->text('email_footer');
            $t->unsignedInteger('industry_id')->nullable();

            $t->string('number_autho')->nullable();
            $t->date('deadline')->nullable();
            $t->string('key_dosage')->nullable();

            $t->string('activity_pri')->nullable();
            $t->string('activity_sec1')->nullable();
            $t->string('activity_sec2')->nullable();

            $t->string('law')->nullable();

            $t->string('title')->nullable();
            $t->string('subtitle')->nullable();

            $t->integer('invoice_number_counter')->default(1)->nullable();
            $t->text('quote_number_prefix')->nullable();
            $t->integer('quote_number_counter')->default(1)->nullable();
            $t->boolean('share_counter')->default(false);

            $t->string('aux1')->nullable();
            $t->string('aux2')->nullable();

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('country_id')->references('id')->on('countries');
            $t->foreign('industry_id')->references('id')->on('industries');
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );     

        });

        Schema::create('users', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('phone')->nullable();
            $t->string('username')->unique();
            $t->string('email')->nullable();
            $t->string('password');
            $t->string('confirmation_code');
            $t->boolean('registered')->default(false);
            $t->boolean('confirmed')->default(false);
            $t->integer('theme_id')->nullable();
            $t->boolean('force_pdfjs')->default(false);  
            $t->string('remember_token', 100)->nullable();

            $t->boolean('is_admin')->default(0);            

            $t->boolean('notify_sent')->default(true);
            $t->boolean('notify_viewed')->default(false);
            $t->boolean('notify_paid')->default(true);

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_id')->references('id')->on('branches');

            $t->unsignedInteger('public_id')->nullable();
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('account_gateways', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('gateway_id');
            $t->timestamps();
            $t->softDeletes();
            
            $t->text('config');

            $t->unsignedInteger('accepted_credit_cards')->nullable();


            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('gateway_id')->references('id')->on('gateways');
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        }); 

        Schema::create('invoice_designs', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->text('name');
            $t->text('javascript')->nullable();
            $t->string('x');
            $t->string('y');

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );


        }); 

        Schema::create('password_reminders', function($t)
        {
            $t->string('email');
            $t->timestamps();
            
            $t->string('token');
        });        

        Schema::create('clients', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('account_id')->index();   
            $t->unsignedInteger('branch_id')->nullable();         
            $t->unsignedInteger('currency_id')->default(1);
            $t->timestamps();
            $t->softDeletes();

            $t->string('nit')->nullable();
            $t->string('name')->nullable();
            $t->string('address1')->nullable();
            $t->string('address2')->nullable();
            $t->string('city')->nullable();
            $t->string('state')->nullable();
            $t->string('postal_code')->nullable();
            $t->unsignedInteger('country_id')->nullable();
            $t->string('work_phone')->nullable();
            $t->text('private_notes')->nullable();
            $t->decimal('balance', 13, 2)->nullable();
            $t->decimal('paid_to_date', 13, 2)->nullable();
            $t->timestamp('last_login')->nullable();
            $t->string('website')->nullable();
            $t->unsignedInteger('industry_id')->nullable();
            $t->unsignedInteger('size_id')->nullable();
            $t->boolean('is_deleted')->default(false);
            $t->integer('payment_terms')->nullable();
            $t->string('vat_number')->nullable();

            $t->string('custom_value1')->nullable();
            $t->string('custom_value2')->nullable();
            $t->string('custom_value3')->nullable();
            $t->string('custom_value4')->nullable();    
            $t->string('custom_value5')->nullable();
            $t->string('custom_value6')->nullable();
            $t->string('custom_value7')->nullable();
            $t->string('custom_value8')->nullable();
            $t->string('custom_value9')->nullable();
            $t->string('custom_value10')->nullable();
            $t->string('custom_value11')->nullable();
            $t->string('custom_value12')->nullable();

            $t->string('aux1')->nullable();
            $t->string('aux2')->nullable();

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('country_id')->references('id')->on('countries');       
            $t->foreign('industry_id')->references('id')->on('industries');       
            $t->foreign('size_id')->references('id')->on('sizes');      
            $t->foreign('currency_id')->references('id')->on('currencies');
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });     

        Schema::create('contacts', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('branch_id')->nullable(); 
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('client_id')->index();
            $t->timestamps();
            $t->softDeletes();

            $t->boolean('is_primary')->default(0);
            $t->boolean('send_invoice')->default(0);
            $t->string('first_name')->nullable();
            $t->string('last_name')->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->timestamp('last_login')->nullable();

            $t->string('aux1')->nullable();
            $t->string('aux2')->nullable();

            $t->foreign('client_id')->references('id')->on('clients'); 
            $t->foreign('user_id')->references('id')->on('users');;

            $t->unsignedInteger('public_id')->nullable();
            $t->unique( array('account_id','public_id') );
        });     

        Schema::create('invoice_statuses', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('frequencies', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('invoices', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('client_id')->index();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('branch_id')->index();
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('invoice_status_id')->default(1);
            $t->timestamps();
            $t->softDeletes();

            $t->string('invoice_number');
            $t->float('discount');
            $t->string('po_number');
            $t->date('invoice_date')->nullable();
            $t->date('due_date')->nullable();
            $t->text('terms');
            $t->text('public_notes');
            $t->boolean('is_deleted')->default(false);            
            $t->boolean('is_recurring');
            $t->unsignedInteger('frequency_id');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->timestamp('last_sent_date')->nullable();  
            $t->unsignedInteger('recurring_invoice_id')->index()->nullable();

            $t->string('account_name');
            $t->string('account_nit');
            $t->string('branch_name');

            $t->string('address1');
            $t->string('address2');
            $t->string('phone');
            $t->string('city');
            $t->string('state');

            $t->string('number_autho');
            $t->date('deadline');
            $t->string('key_dosage');

            $t->string('client_nit');
            $t->string('client_name');

            $t->string('activity_pri');
            $t->string('activity_sec1');
            $t->string('activity_sec2');

            $t->string('law');

            $t->string('title');
            $t->string('subtitle');

            $t->string('control_code');

            $t->longText('qr');

            $t->string('aux1')->nullable();
            $t->string('aux2')->nullable(); 

            $t->string('tax_name');
            $t->decimal('tax_rate', 13, 2);

            $t->decimal('subtotal', 13, 2);
            $t->decimal('amount', 13, 2);
            $t->decimal('balance', 13, 2);

            $t->decimal('fiscal', 13, 2);
            $t->decimal('ice', 13, 2);

            $t->boolean('is_quote')->default(0);            
            $t->unsignedInteger('quote_id')->nullable();
            $t->unsignedInteger('quote_invoice_id')->nullable();


            $t->decimal('custom_value1', 13, 2)->default(0);
            $t->decimal('custom_value2', 13, 2)->default(0);

            $t->boolean('custom_taxes1')->default(0);
            $t->boolean('custom_taxes2')->default(0);

            $t->unsignedInteger('invoice_design_id');
        
            $t->foreign('client_id')->references('id')->on('clients');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users'); 
            $t->foreign('invoice_status_id')->references('id')->on('invoice_statuses');
            $t->foreign('recurring_invoice_id')->references('id')->on('invoices');
            $t->foreign('invoice_design_id')->references('id')->on('invoice_designs');


            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });


        Schema::create('invitations', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('contact_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->string('invitation_key')->index()->unique();
            $t->timestamps();
            $t->softDeletes();

            $t->string('transaction_reference')->nullable();
            $t->timestamp('sent_date');
            $t->timestamp('viewed_date');

            $t->foreign('user_id')->references('id')->on('users');;
            $t->foreign('contact_id')->references('id')->on('contacts');
            $t->foreign('invoice_id')->references('id')->on('invoices');

            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('tax_rates', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('name');
            $t->decimal('rate', 13, 2);
            
            $t->foreign('account_id')->references('id')->on('accounts'); 
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('user_id')->references('id')->on('users');;
            
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('categories', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('category_key');
            $t->text('name');
            $t->text('description');
            $t->text('aux');
         
            $t->foreign('account_id')->references('id')->on('accounts'); 
            $t->foreign('branch_id')->references('id')->on('branches');            
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('products', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('category_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('product_key');
            $t->text('notes');
            $t->decimal('cost', 13, 2);
            $t->decimal('qty', 13, 2)->nullable();
            
            $t->foreign('account_id')->references('id')->on('accounts'); 
            $t->foreign('branch_id')->references('id')->on('branches');            
            $t->foreign('category_id')->references('id')->on('categories');     
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('invoice_items', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->unsignedInteger('product_id')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->string('product_key');
            $t->text('notes');
            $t->decimal('cost', 13, 2);
            $t->decimal('qty', 13, 2)->nullable();            

            $t->string('tax_name')->nullable();
            $t->decimal('tax_rate', 13, 2)->nullable();

            $t->foreign('invoice_id')->references('id')->on('invoices');
            $t->foreign('product_id')->references('id')->on('products');
            $t->foreign('user_id')->references('id')->on('users');;

            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id') );
        });

        Schema::create('payments', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('invoice_id')->nullable();
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('client_id')->index();
            $t->unsignedInteger('contact_id')->nullable();
            $t->unsignedInteger('invitation_id')->nullable();
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('account_gateway_id')->nullable();
            $t->unsignedInteger('payment_type_id')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->boolean('is_deleted')->default(false);
            $t->decimal('amount', 13, 2);
            $t->date('payment_date')->nullable();
            $t->string('transaction_reference')->nullable();
            $t->string('payer_id')->nullable();

            $t->foreign('invoice_id')->references('id')->on('invoices'); 
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('client_id')->references('id')->on('clients');
            $t->foreign('contact_id')->references('id')->on('contacts');
            $t->foreign('account_gateway_id')->references('id')->on('account_gateways');
            $t->foreign('user_id')->references('id')->on('users');;
            $t->foreign('payment_type_id')->references('id')->on('payment_types');
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });     

        Schema::create('credits', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('client_id')->index();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();
            
            $t->boolean('is_deleted')->default(false);
            $t->decimal('amount', 13, 2);
            $t->decimal('balance', 13, 2);
            $t->date('credit_date')->nullable();
            $t->string('credit_number')->nullable();
            $t->text('private_notes');
            
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('client_id')->references('id')->on('clients');
            $t->foreign('user_id')->references('id')->on('users');;
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id') );
        });     

        Schema::create('activities', function($t)
        {
            $t->increments('id');
            $t->timestamps();

            $t->unsignedInteger('account_id');
            $t->unsignedInteger('client_id');
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('contact_id')->nullable();
            $t->unsignedInteger('payment_id')->nullable();
            $t->unsignedInteger('invoice_id')->nullable();
            $t->unsignedInteger('credit_id')->nullable();
            $t->unsignedInteger('invitation_id')->nullable();
            
            $t->text('message')->nullable();
            $t->text('json_backup')->nullable();
            $t->integer('activity_type_id');            
            $t->decimal('adjustment', 13, 2)->nullable();
            $t->decimal('balance', 13, 2)->nullable();
            
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('client_id')->references('id')->on('clients');
        });

        Schema::create('manuals', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->timestamps();
            $t->softDeletes(); 

            $t->string('invoice_date');
            $t->string('invoice_number');
            $t->string('number_autho');
            $t->string('status');

            $t->string('client_nit');
            $t->string('client_name');

            $t->decimal('amount', 13, 2);

            $t->decimal('ice_amount', 13, 2);
            $t->decimal('export_amount', 13, 2);
            $t->decimal('grav_amount', 13, 2);

            $t->decimal('subtotal', 13, 2);

            $t->decimal('disc_bonus_amount', 13, 2);

            $t->decimal('base_fiscal_debit_amount', 13, 2);
            $t->decimal('fiscal_debit_amount', 13, 2);
            
            $t->string('control_code');

            $t->text('private_notes');


            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('purchases', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->timestamps();
            $t->softDeletes();

            $t->string('invoice_date');

            $t->string('provider_nit');
            $t->string('provider_name');

            $t->string('invoice_number');

            $t->string('dui_number');

            $t->string('number_autho');

            $t->decimal('amount', 13, 2);

            $t->decimal('no_fiscal_amount', 13, 2);

            $t->decimal('subtotal', 13, 2);

            $t->decimal('disc_bonus_amount', 13, 2);

            $t->decimal('base_fiscal_debit_amount', 13, 2);

            $t->decimal('fiscal_credit_amount', 13, 2);
         
            $t->string('control_code');

            $t->string('purchase_type');

            $t->text('private_notes');

            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('book_sales', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
			$t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->timestamps();
            $t->softDeletes();

            $t->string('invoice_date');
            $t->string('invoice_number');
            $t->string('number_autho');
			$t->string('status');

			$t->string('client_nit');
            $t->string('client_name');

            $t->decimal('amount', 13, 2);

            $t->decimal('ice_amount', 13, 2);
            $t->decimal('export_amount', 13, 2);
            $t->decimal('grav_amount', 13, 2);

            $t->decimal('subtotal', 13, 2);

            $t->decimal('disc_bonus_amount', 13, 2);

            $t->decimal('base_fiscal_debit_amount', 13, 2);
            $t->decimal('fiscal_debit_amount', 13, 2);
            
            $t->string('control_code');

            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
        });
		
		Schema::create('book_purchases', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
			$t->unsignedInteger('branch_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->timestamps();
            $t->softDeletes();

            $t->string('invoice_date');

            $t->string('provider_nit');
            $t->string('provider_name');

            $t->string('invoice_number');

            $t->string('dui_number');

            $t->string('number_autho');

            $t->decimal('amount', 13, 2);

            $t->decimal('no_fiscal_amount', 13, 2);

            $t->decimal('subtotal', 13, 2);

            $t->decimal('disc_bonus_amount', 13, 2);

            $t->decimal('base_fiscal_debit_amount', 13, 2);

            $t->decimal('fiscal_credit_amount', 13, 2);
         
            $t->string('control_code');

            $t->string('purchase_type');

            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_terms');             
        Schema::dropIfExists('themes');        
        Schema::dropIfExists('credits');        
        Schema::dropIfExists('activities');
        Schema::dropIfExists('invitations');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('account_gateways');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('products');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('password_reminders');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('users');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('currencies');        
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('timezones');        
        Schema::dropIfExists('frequencies');        
        Schema::dropIfExists('date_formats');        
        Schema::dropIfExists('datetime_formats');                      
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('industries');
        Schema::dropIfExists('gateways');        
        Schema::dropIfExists('payment_types');
        Schema::dropIfExists('book_sales');
        Schema::dropIfExists('book_purchases');
        Schema::dropIfExists('manuals');
        
    }
}
