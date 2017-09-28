<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentMetasTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('document_metas', function(Blueprint $table)
		{
            $table->string('id', 36)->primary();
            $table->string('document_id', 36);
            $table->integer('meta_group')->default(0);
            $table->text('extras');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->index('document_id');
            $table->index('meta_group');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('document_metas');
	}

}
