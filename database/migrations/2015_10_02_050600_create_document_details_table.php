<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocumentDetailsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('document_details', function(Blueprint $table)
		{
			$table->string('id', 36)->primary();
            $table->string('document_meta_id', 36);
            $table->string('document_name', 50);
            $table->string('status', 20);
            $table->text('notes');
            $table->string('checked_by', 36);
            $table->tinyInteger('is_active')->default(1);
			$table->timestamps();

            $table->engine = 'InnoDB';
            $table->index(['document_meta_id', 'is_active']);
            $table->index('checked_by');
            $table->index('status');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('document_details');
	}

}
