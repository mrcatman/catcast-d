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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('role_id')->default(0);
            $table->string('full_name')->nullable();
            $table->string('username');
            $table->string('domain')->nullable();
            $table->string('password')->nullable();
            $table->string('about', 1024)->nullable();
            $table->dateTime('last_seen')->nullable();
            $table->string('last_ip_address', 24)->nullable();
            $table->timestamps();
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
            $table->text('actor_id')->nullable();
            $table->text('key_id')->nullable();
            $table->text('inbox_url')->nullable();
            $table->text('outbox_url')->nullable();
            $table->text('shared_inbox_url')->nullable();
            $table->text('web_url')->nullable();
            $table->string('status_text')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
