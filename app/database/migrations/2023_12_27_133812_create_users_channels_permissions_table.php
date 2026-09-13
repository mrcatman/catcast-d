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
        Schema::create('users_channels_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('channel_id');
            $table->integer('user_id');
            $table->text('permissions');
            $table->boolean('hidden')->default(false);
            $table->string('position')->nullable();
            $table->boolean('confirmed')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->timestamp('left_at')->nullable();
            $table->integer('added_by_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_channels_permissions');
    }
};
