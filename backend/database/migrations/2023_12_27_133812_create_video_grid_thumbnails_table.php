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
        Schema::create('video_grid_thumbnails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('video_id');
            $table->integer('width');
            $table->integer('height');
            $table->integer('every_nth_second');
            $table->string('frames_count');
            $table->timestamps();
            $table->integer('picture_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_grid_thumbnails');
    }
};
