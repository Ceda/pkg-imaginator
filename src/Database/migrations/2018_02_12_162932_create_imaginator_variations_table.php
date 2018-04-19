<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImaginatorVariationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('imaginator_variations', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('imaginator_template_id')->unsigned()->nullable();
			$table->foreign('imaginator_template_id')
				->references('id')
				->on('imaginator_templates')
				->onDelete('cascade');
			$table->string('name');
			$table->string('slug')->nullable();
			$table->string('breakpoint', 25)->nullable();
			$table->string('locale', 10)->nullable();
			$table->string('density', 25)->nullable();
			$table->integer('quality')->nullable();
			$table->integer('width')->nullable();
			$table->integer('height')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('imaginator_variations');
	}
}
