<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplatesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('templates', function(Blueprint $table)
		{
            $table->string('id', 36)->primary();
            $table->string('batch_id', 36);
            $table->string('company_id', 36);
            $table->string('template_name', 45);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->unique(['template_name', 'is_active']);
            $table->index('batch_id');
            $table->index('company_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('templates');
	}

}
