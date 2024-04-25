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
        Schema::create('radio_files_playlists', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('radio_file_id');
            $table->integer('playlist_id');
            $table->timestamps();
            $table->integer('index')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('radio_files_playlists');
    }
};
