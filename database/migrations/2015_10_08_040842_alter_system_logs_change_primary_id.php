<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSystemLogsChangePrimaryId extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('system_logs', function(Blueprint $table)
        {
            $table->string('id',36)->first()->primary();
            $sql = 'ALTER TABLE system_logs DROP object_id';
            DB::connection()->getPdo()->exec($sql);
        });


	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
