<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUploadNameInBatch extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('batch', function(Blueprint $table)
        {
            $table->string('upload_name', 100)->after('id');
            $table->index('upload_name');
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
            $table->dropColumn('upload_name');
        });
	}

}
