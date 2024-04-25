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
        Schema::create('radio_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id');
            $table->integer('user_id')->nullable();
            $table->integer('folder_id')->nullable();
            $table->string('filename', 1000);
            $table->string('folder', 1000);
            $table->string('author')->nullable();
            $table->string('title')->nullable();
            $table->string('album')->nullable();
            $table->text('description')->nullable();
            $table->string('domain')->nullable();
            $table->string('url', 1000);
            $table->integer('file_size');
            $table->integer('length');
            $table->boolean('is_public')->default(false);
            $table->integer('upload_status')->default(-1);
            $table->integer('views')->default(0);
            $table->timestamps();
            $table->dateTime('deleted_at')->nullable();
            $table->string('object_id')->nullable();
            $table->text('waveform_data')->nullable();
            $table->boolean('is_recording')->default(false);
            $table->string('folders')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('radio_files');
    }
};
