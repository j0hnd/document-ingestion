<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSiteIdInUserSites extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('user_sites', function(Blueprint $table)
        {
            $table->string('site_id', 36)->after('user_id');
            $table->index('site_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_sites', function(Blueprint $table)
        {
            $table->dropColumn('site_id');
        });
    }

}
