<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImaginatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imaginators', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('imaginator_template_id')->unsigned()->nullable();
            $table->foreign('imaginator_template_id')
        ->references('id')
        ->on('imaginator_templates')
        ->onDelete('cascade');
            $table->string('alias')->nullable();
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
        Schema::dropIfExists('imaginators');
    }
}
