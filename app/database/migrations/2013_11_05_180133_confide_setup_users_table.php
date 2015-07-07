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

        Schema::create('languages', function($table)
        {
          $table->increments('id');
          $table->string('name');
          $table->string('locale');
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

        Schema::create('accounts', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('currency_id')->nullable();
            $t->unsignedInteger('language_id')->nullable();
            $t->unsignedInteger('timezone_id')->nullable();
            $t->unsignedInteger('date_format_id')->nullable();
            $t->unsignedInteger('datetime_format_id')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->string('ip');
            $t->string('account_key')->unique();
            $t->timestamp('last_login')->nullable();

            $t->string('nit')->unique();
            $t->string('name');
            
            $t->string('address1');
            $t->string('address2');
            $t->string('city');
            $t->string('state');
            $t->string('work_phone');

            $t->boolean('confirmed')->default(false);

            $t->integer('credit_counter')->default(0);

            $t->boolean('op1')->default(false);
            $t->boolean('op2')->default(false);
            $t->boolean('op3')->default(false);

            $t->date('billing_deadline')->null();

            $t->boolean('is_uniper')->default(false);
            $t->string('uniper')->nullable();

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

            $t->boolean('fill_products')->default(true);
            $t->boolean('update_products')->default(true);

            $t->foreign('currency_id')->references('id')->on('currencies');
            $t->foreign('language_id')->references('id')->on('languages');
        });        

        Schema::create('branch_types', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('branches', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_type_id');

            $t->timestamps();
            $t->softDeletes();
            
            $t->string('name');

            $t->string('address1');
            $t->string('address2');
            $t->string('city');
            $t->string('state');
            $t->string('work_phone');

            $t->string('number_process');
            $t->string('number_autho');
            $t->date('deadline');
            $t->string('key_dosage');

            $t->string('economic_activity');

            $t->string('law');

            $t->boolean('type_third')->default(false);

            $t->integer('invoice_number_counter')->default(0);
           
            $t->text('quote_number_prefix')->nullable();
            $t->integer('quote_number_counter')->default(0)->nullable();       

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_type_id')->references('id')->on('branch_types');

            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id'));     
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

            $t->boolean('registered')->default(true);
            $t->boolean('confirmed')->default(true); 
            $t->string('remember_token', 100)->nullable();

            $t->boolean('is_admin')->default(0);            

            $t->boolean('notify_sent')->default(true);
            $t->boolean('notify_viewed')->default(true);
            $t->boolean('notify_paid')->default(true);

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->unsignedInteger('public_id')->nullable();
            $t->unique( array('account_id','public_id'));
        });      

        Schema::create('clients', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index(); 
            $t->unsignedInteger('user_id');    

            $t->timestamps();
            $t->softDeletes();

            $t->string('name');
            $t->string('business_name')->nullable();
            $t->string('nit');

            $t->string('address1')->nullable();
            $t->string('address2')->nullable();
            $t->string('city')->nullable();
            $t->string('state')->nullable();

            $t->string('work_phone')->nullable();
            $t->text('private_notes')->nullable();

            $t->decimal('balance', 13, 2)->nullable();
            $t->decimal('paid_to_date', 13, 2)->nullable();

            $t->timestamp('last_login')->nullable();
            $t->boolean('is_deleted')->default(false);

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

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');                
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id'));
        });     

        Schema::create('contacts', function($t)
        {
            $t->increments('id');         
            $t->unsignedInteger('account_id');
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

            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('client_id')->references('id')->on('clients'); 

            $t->unsignedInteger('public_id')->nullable();
            $t->unique( array('account_id','public_id'));
        });     


        Schema::create('invoice_designs', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('user_id');

            $t->text('logo');
            $t->text('javascript')->nullable();
            $t->string('x');
            $t->string('y');

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id'));
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
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('branch_id')->index();
            $t->unsignedInteger('branch_type_id');
            $t->unsignedInteger('client_id');
            $t->unsignedInteger('user_id');
            
            $t->unsignedInteger('invoice_design_id');
            $t->unsignedInteger('invoice_status_id')->default(1);
            $t->unsignedInteger('frequency_id');
            $t->unsignedInteger('recurring_invoice_id')->index()->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->string('invoice_number');
            $t->date('invoice_date')->nullable();
            $t->date('due_date')->nullable();
            $t->float('discount');
            $t->string('po_number');

            $t->text('terms');
            $t->text('public_notes');

            $t->boolean('is_deleted')->default(false);            
            $t->boolean('is_recurring');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->timestamp('last_sent_date')->nullable();  

            $t->string('account_name');
            $t->string('account_nit');
            $t->string('account_uniper');
            
            $t->string('branch_name');
            $t->string('address1');
            $t->string('address2');
            $t->string('phone');
            $t->string('city');
            $t->string('state');
            $t->string('number_autho');
            $t->date('deadline');
            $t->string('key_dosage');
            $t->boolean('type_third')->default(false);

            $t->string('client_nit');
            $t->string('client_name');

            $t->string('economic_activity');

            $t->string('law');

            $t->string('control_code');

            $t->string('qr');

            $t->decimal('subtotal', 13, 2);
            $t->decimal('amount', 13, 2);
            $t->decimal('balance', 13, 2);

            $t->decimal('fiscal', 13, 2);
            $t->decimal('ice', 13, 2);

            $t->boolean('is_quote')->default(0); 

            $t->unsignedInteger('quote_id')->nullable();
            $t->unsignedInteger('quote_invoice_id')->nullable();

            $t->foreign('client_id')->references('id')->on('clients');
            $t->foreign('branch_id')->references('id')->on('branches');
            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('branch_type_id')->references('id')->on('branch_types');
            $t->foreign('user_id')->references('id')->on('users'); 
            $t->foreign('invoice_status_id')->references('id')->on('invoice_statuses');
            $t->foreign('recurring_invoice_id')->references('id')->on('invoices');
            $t->foreign('invoice_design_id')->references('id')->on('invoice_designs');

            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id'));
        });


        Schema::create('invitations', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('contact_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->string('invitation_key')->index()->unique();
            $t->timestamps();
            $t->softDeletes();

            $t->string('transaction_reference')->nullable();
            $t->timestamp('sent_date');
            $t->timestamp('viewed_date');

            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('contact_id')->references('id')->on('contacts');
            $t->foreign('invoice_id')->references('id')->on('invoices');

            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id'));
        });

        Schema::create('tax_rates', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('name');
            $t->decimal('rate', 13, 2);
            
            $t->foreign('account_id')->references('id')->on('accounts'); 
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id'));
        });

        Schema::create('categories', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->text('name');
            $t->text('description');
         
            $t->foreign('account_id')->references('id')->on('accounts');             
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id'));
        });

        Schema::create('products', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('category_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->timestamps();
            $t->softDeletes();

            $t->string('product_key');
            $t->text('notes');
            $t->decimal('cost', 13, 2);
            $t->decimal('qty', 13, 2)->nullable();
            
            $t->foreign('account_id')->references('id')->on('accounts');            
            $t->foreign('category_id')->references('id')->on('categories');     
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id'));
        });

        Schema::create('invoice_items', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('invoice_id')->index();
            $t->unsignedInteger('product_id')->nullable();
            $t->timestamps();
            $t->softDeletes();

            $t->string('product_key');
            $t->text('notes');
            $t->decimal('cost', 13, 2);
            $t->decimal('qty', 13, 2)->nullable();
            $t->float('discount');            

            $t->foreign('invoice_id')->references('id')->on('invoices');
            $t->foreign('product_id')->references('id')->on('products');
            $t->foreign('user_id')->references('id')->on('users');

            $t->unsignedInteger('public_id');
            $t->unique( array('account_id','public_id'));
        });

        Schema::create('payment_types', function($t)
        {
            $t->increments('id');
            $t->string('name');
        });

        Schema::create('payments', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('invoice_id')->nullable();
            $t->unsignedInteger('account_id')->index();
            $t->unsignedInteger('client_id')->index();
            $t->unsignedInteger('contact_id')->nullable();
            $t->unsignedInteger('invitation_id')->nullable();
            $t->unsignedInteger('user_id')->nullable();
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
            $t->foreign('client_id')->references('id')->on('clients');
            $t->foreign('contact_id')->references('id')->on('contacts');
            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('payment_type_id')->references('id')->on('payment_types');
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id'));
        });     

        Schema::create('credits', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id')->index();
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
            $t->foreign('client_id')->references('id')->on('clients');
            $t->foreign('user_id')->references('id')->on('users');
            
            $t->unsignedInteger('public_id')->index();
            $t->unique( array('account_id','public_id'));
        });     

        Schema::create('activities', function($t)
        {
            $t->increments('id');
            $t->timestamps();

            $t->unsignedInteger('account_id');
            $t->unsignedInteger('client_id');
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
            $t->foreign('client_id')->references('id')->on('clients');
        });

        Schema::create('manuals', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
            $t->unsignedInteger('branch_id');
            $t->unsignedInteger('user_id');
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

        Schema::create('book_sales', function($t)
        {
            $t->increments('id');
            $t->unsignedInteger('account_id');
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

            $t->foreign('account_id')->references('id')->on('accounts');
            $t->foreign('user_id')->references('id')->on('users');
        });
		
        Schema::create('password_reminders', function($t)
        {
            $t->string('email');
            $t->timestamps();
            
            $t->string('token');
        });  

    }
    
}
