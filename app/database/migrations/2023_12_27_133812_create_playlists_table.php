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
        Schema::create('playlists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id');
            $table->integer('user_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('links')->nullable();
            $table->integer('views')->default(0);
            $table->string('object_id')->nullable();
            $table->string('colors_scheme', 512)->nullable();
            $table->timestamps();
            $table->boolean('use_custom_design')->nullable()->default(false);
            $table->integer('privacy_status')->nullable()->default(0);
            $table->string('uuid', 21)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('playlists');
    }
};
