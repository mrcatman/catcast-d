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
        Schema::create('radio_queue', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id');
            $table->integer('track_id');
            $table->integer('index')->nullable();
            $table->boolean('was_played')->default(false);
            $table->dateTime('play_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('radio_queue');
    }
};
