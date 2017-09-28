<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableUserDetails extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_details', function(Blueprint $table)
		{
			$table->string('id', 36)->primary();
			$table->string('firstname', 50);
			$table->string('lastname', 50);
			$table->string('username', 30)->unique();
			$table->string('password', 100);
			$table->string('account_type', 20);
			$table->string('gender', 10);
			$table->string('college', 50);
			$table->string('address', 150);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_details');
	}

}
