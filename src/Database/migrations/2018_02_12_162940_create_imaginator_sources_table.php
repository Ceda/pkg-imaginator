<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImaginatorSourcesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('imaginator_sources', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('imaginator_variation_id')->unsigned()->nullable();
      $table->foreign('imaginator_variation_id')
        ->references('id')
        ->on('imaginator_variations')
        ->onDelete('cascade');
      $table->integer('imaginator_id')->unsigned()->nullable();
      $table->foreign('imaginator_id')
        ->references('id')
        ->on('imaginators')
        ->onDelete('cascade');
      $table->text('source')->nullable();
      $table->text('resized')->nullable();
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
    Schema::dropIfExists('imaginator_sources');
  }
}
