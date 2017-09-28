<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIAdminInUserTypes extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::table('user_types', function(Blueprint $table)
        {
            $table->tinyInteger('is_admin')->after('is_active')->default(0);
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
            $table->dropColumn('is_admin');
        });
    }

}
