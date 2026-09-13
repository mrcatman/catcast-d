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
        Schema::create('autopilot_folders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('playlist_id');
            $table->integer('user_id')->nullable();
            $table->integer('index');
            $table->string('title');
            $table->tinyInteger('cycled')->nullable();
            $table->integer('cycled_till')->nullable();
            $table->integer('limit_length');
            $table->boolean('can_subscribe')->default(true);
            $table->boolean('can_view')->default(true);
            $table->text('description')->nullable();
            $table->text('additional_data')->nullable();
            $table->tinyInteger('show_contents')->nullable();
            $table->integer('playback_type')->default(0);
            $table->integer('video_folder_id')->nullable();
            $table->boolean('is_synchronised')->nullable();
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
        Schema::dropIfExists('autopilot_folders');
    }
};
