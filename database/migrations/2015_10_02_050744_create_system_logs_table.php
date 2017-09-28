<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemLogsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('system_logs', function(Blueprint $table)
		{
            $table->string('object_id', 36);
            $table->string('action', 100);
            $table->string('source', 45);
            $table->string('old_value', 100);
            $table->string('new_value', 100);
            $table->string('action_by', 36);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index('object_id');
            $table->index(['action', 'source', 'is_active']);
            $table->index('action_by');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('system_logs');
	}

}
