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
        Schema::create('pictures_connections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('picture_id');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('entity_picture_type');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pictures_connections');
    }
};
