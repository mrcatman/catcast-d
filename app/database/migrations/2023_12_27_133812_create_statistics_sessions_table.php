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
        Schema::create('statistics_sessions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('device_hash');
            $table->string('entity_type');
            $table->integer('entity_id');
            $table->timestamps();
            $table->string('country_code', 4)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statistics_sessions');
    }
};
