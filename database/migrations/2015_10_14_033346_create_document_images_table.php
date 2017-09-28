<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentImagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('document_images', function(Blueprint $table)
        {
            $table->string('id', 36)->primary();
            $table->string('document_id', 36);
            $table->string('filename', 100);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index('document_id');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('document_images');
	}

}
