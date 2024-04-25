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
        Schema::create('playlists_media', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('playlist_id');
            $table->integer('index')->default(0);
            $table->unsignedBigInteger('media_id')->index('projects_items_item_type_item_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('playlists_media');
    }
};
