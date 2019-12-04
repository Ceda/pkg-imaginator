<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlugImaginatorVariationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('imaginator_variations', function (Blueprint $table) {
      $table->string('slug')->nullable()->index()->after('name');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('imaginator_variations', function (Blueprint $table) {
      $table->dropindex(['slug']);
      $table->dropColumn(['slug']);
    });
  }
}
