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
        Schema::create('radio_playlists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->integer('channel_id');
            $table->integer('last_play_index')->nullable();
            $table->integer('last_play_track_index')->nullable();
            $table->integer('last_play_time')->nullable();
            $table->string('title');
            $table->string('description', 5000);
            $table->integer('playback_weight');
            $table->integer('playback_type');
            $table->integer('playback_order');
            $table->string('playback_data', 5000);
            $table->string('cover');
            $table->integer('created_by');
            $table->boolean('is_visible');
            $table->boolean('is_special');
            $table->softDeletes();
            $table->string('last_play_date')->nullable();
            $table->integer('already_played_tracks')->nullable();
            $table->boolean('can_accept_requests')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('radio_playlists');
    }
};
