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
        Schema::create('radio_playback_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('local_index');
            $table->integer('channel_id');
            $table->integer('radio_file_id');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('playlist_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('radio_playback_history');
    }
};
