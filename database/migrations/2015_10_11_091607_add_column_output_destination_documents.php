<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOutputDestinationDocuments extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
//        Schema::table('documents', function(Blueprint $table)
//        {
//            $table->string('output_destination_id', 36)->after('s3_key_name');
//            $table->index('output_destination_id');
//        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
//        Schema::table('documents', function(Blueprint $table)
//        {
//            $table->dropColumn('output_destination');
//        });
	}

}
