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
        Schema::create('users_interact_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->integer('status');
            $table->timestamps();

            $table->index(['entity_type', 'entity_id'], 'users_permissions_entity_type_entity_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_interact_permissions');
    }
};
