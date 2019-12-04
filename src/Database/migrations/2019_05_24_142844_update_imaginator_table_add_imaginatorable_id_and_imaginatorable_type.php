<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateImaginatorTableAddImaginatorableIdAndImaginatorableType extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('imaginators', function (Blueprint $table) {
      $table->unsignedInteger('imaginatorable_id')->nullable()->after('imaginator_template_id');
      $table->string('imaginatorable_type')->nullable()->after('imaginatorable_id');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('imaginators', function (Blueprint $table) {
      $table->dropColumn(['imaginatorable_id', 'imaginatorable_type']);
    });
  }
}
