<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('template_details', function(Blueprint $table)
		{
            $table->string('id', 36)->primary();
            $table->string('template_id', 36);
            $table->string('required_key', 50);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index(['template_id', 'required_key', 'is_active']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('template_details');
	}

}
