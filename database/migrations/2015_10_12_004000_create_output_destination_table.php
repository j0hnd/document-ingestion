<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOutputDestinationTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('output_destinations', function(Blueprint $table)
        {
            $table->string('id', 36)->primary();
            $table->string('output_destination_name', 50);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index(['output_destination_name', 'is_active']);
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('output_destinations');
	}

}
