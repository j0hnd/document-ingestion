<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSitesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::create('user_sites', function(Blueprint $table)
        {
            $table->string('id', 36)->primary();
            $table->string('user_id', 100);
            $table->string('user_site_id', 100);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index('user_id');
            $table->index('user_site_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('user_sites');
    }

}
