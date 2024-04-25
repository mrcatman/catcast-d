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
        Schema::create('notification_bindings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->timestamps();
            $table->string('event_type')->nullable();
            $table->string('notification_channel_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_bindings');
    }
};
