<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetaDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
    public function up()
    {
        Schema::create('meta_details', function(Blueprint $table)
        {
            $table->string('id', 36)->primary();
            $table->string('document_meta_id', 36);
            $table->integer('details_group')->default(0);
            $table->integer('meta_order')->default(0);
            $table->string('key', 100);
            $table->string('value', 100)->nullable();
            $table->timestamps();

            $table->index('document_meta_id');
            $table->index('details_group');

            $table->engine = 'InnoDB';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('meta_details');
    }

}
