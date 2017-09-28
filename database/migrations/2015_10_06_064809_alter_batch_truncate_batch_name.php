<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterBatchTruncateBatchName extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('batch', function(Blueprint $table)
        {
            $table->dropColumn('batch_name');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('batch', function(Blueprint $table)
        {
            $table->dropColumn('batch_name');
        });
	}

}
