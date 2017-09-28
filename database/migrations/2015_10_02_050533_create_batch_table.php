<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBatchTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('batch', function(Blueprint $table)
		{
            $table->string('id', 36)->primary();
            $table->string('batch_name', 50);
            $table->string('source', 40);
            $table->string('status', 20);
            $table->tinyInteger('is_active')->default(1);
            $table->string('created_by', 36);
            $table->string('updated_by', 36);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->unique(['batch_name', 'is_active']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('batch');
	}

}
