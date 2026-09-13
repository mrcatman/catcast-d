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
        Schema::create('autopilot_temp_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id')->index('channel_id_index');
            $table->integer('repeat_count')->nullable();
            $table->integer('playlist_id');
            $table->integer('folder_id')->nullable();
            $table->string('item_id', 50);
            $table->integer('time_start')->index('time_start_index');
            $table->integer('time_end')->index('time_end_index');
            $table->integer('length');
            $table->integer('length_total');
            $table->string('title');
            $table->text('data');
            $table->timestamps();
            $table->integer('clip_index')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autopilot_temp_items');
    }
};
