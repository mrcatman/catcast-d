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
        Schema::create('autopilot_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('playlist_id')->index('playlist_id_index');
            $table->integer('video_id');
            $table->integer('index');
            $table->string('title');
            $table->string('length');
            $table->boolean('can_subscribe')->default(true);
            $table->boolean('can_view')->default(true);
            $table->text('description')->nullable();
            $table->text('additional_data')->nullable();
            $table->timestamps();
            $table->integer('user_id')->nullable();
            $table->integer('folder_id')->nullable()->index('folder_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('autopilot_items');
    }
};
