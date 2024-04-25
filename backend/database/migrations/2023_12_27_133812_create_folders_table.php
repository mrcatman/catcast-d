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
        Schema::create('folders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id');
            $table->integer('user_id');
            $table->string('title');
            $table->integer('parent_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->integer('connected_project_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('folders');
    }
};
