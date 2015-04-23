<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLanguageSupport extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
	    Schema::create('languages', function($table)
	    {
	      $table->increments('id');
	      $table->string('name');      
	      $table->string('locale');      
	    });

	    DB::table('languages')->insert(['name' => 'Español', 'locale' => 'es']);

		Schema::table('accounts', function($table)
		{
			$table->unsignedInteger('language_id')->default(1);
		});

		DB::table('accounts')->update(['language_id' => 1]);

		Schema::table('accounts', function($table)
		{
			$table->foreign('language_id')->references('id')->on('languages');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('accounts', function($table)
		{
			$table->dropForeign('accounts_language_id_foreign');
			$table->dropColumn('language_id');
		});

		Schema::drop('languages');
	}

}
