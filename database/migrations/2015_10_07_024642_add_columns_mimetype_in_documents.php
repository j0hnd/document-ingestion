<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsMimetypeInDocuments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::table('documents', function(Blueprint $table)
        {
            $table->string('mime_type', 100)->after('filesize');
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
            $table->dropColumn('mime_type');
        });
	}

}
