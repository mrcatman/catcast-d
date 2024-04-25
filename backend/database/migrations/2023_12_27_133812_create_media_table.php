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
        Schema::create('media', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->integer('channel_id');
            $table->integer('user_id')->nullable();
            $table->string('title', 512);
            $table->text('description')->nullable();
            $table->integer('folder_id')->nullable();
            $table->integer('likes_count')->default(0);
            $table->integer('views')->default(0);
            $table->integer('duration')->default(0);
            $table->timestamps();
            $table->string('object_id')->nullable();
            $table->integer('thumbnail_id')->nullable();
            $table->integer('privacy_status')->nullable()->default(0);
            $table->string('uuid', 21)->nullable();
            $table->smallInteger('media_type')->nullable();

            $table->unique(['id'], 'videos_id_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
};
