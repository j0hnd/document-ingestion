<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsFilesizeInDocuments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('documents', function(Blueprint $table)
        {
            $table->integer('filesize')->after('filename');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::table('practitioner_availability', function(Blueprint $table)
        {
            $table->dropColumn('filesize');
        });
	}

}
