<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterSystemLogsSetNewValueNullable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('system_logs', function(Blueprint $table)
        {
            $sql = 'ALTER TABLE system_logs CHANGE new_value new_value TEXT NULL';
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
