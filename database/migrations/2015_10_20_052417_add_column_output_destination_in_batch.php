<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnOutputDestinationInBatch extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('batch', function(Blueprint $table)
        {
            $table->string('output_destination_id', 36)->after('source');
            $table->index('output_destination_id');
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
            $table->dropColumn('output_destination');
        });
    }

}
