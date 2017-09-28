<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnUserSiteIdInUserSites extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
//        Schema::table('user_sites', function(Blueprint $table)
//        {
//            $table->string('user_site_id')->after('user_id');
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Schema::table('user_sites', function(Blueprint $table)
//        {
//            $table->dropColumn('user_site_id');
//        });
    }

}
