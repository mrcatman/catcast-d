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
        Schema::create('account_connections', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('confirm_code')->nullable();
            $table->tinyInteger('confirmed')->default(0);
            $table->string('account_type', 10);
            $table->string('account_name');
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_connections');
    }
};
