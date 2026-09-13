<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('entity_id');
            $table->string('key');
            $table->text('values');
            $table->timestamps();
            $table->string('entity_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('additional_settings');
    }
};
