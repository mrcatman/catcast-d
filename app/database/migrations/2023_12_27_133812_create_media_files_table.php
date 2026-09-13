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
        Schema::create('media_files', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->bigInteger('media_id');
            $table->string('type')->default('TYPE_LOCAL');
            $table->bigInteger('file_size')->default(0);
            $table->string('url');
            $table->timestamps();
            $table->integer('quality')->nullable();
            $table->tinyInteger('original')->nullable()->default(0);

            $table->unique(['id'], 'video_files_id_uindex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_files');
    }
};
