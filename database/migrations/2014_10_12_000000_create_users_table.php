<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->string('id', 36)->primary();
			$table->string('firstname', 30);
			$table->string('lastname', 30);
			$table->string('email')->unique();
			$table->string('password', 100);
            $table->tinyInteger('is_active')->default(0);
			$table->rememberToken();
			$table->timestamps();

            $table->unique(['firstname', 'lastname', 'is_active']);
            $table->index('email');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
