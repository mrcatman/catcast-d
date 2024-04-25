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
        Schema::create('channels', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('shortname');
            $table->string('domain')->nullable();
            $table->string('colors_scheme', 512)->nullable();
            $table->integer('broadcast_id')->nullable();
            $table->integer('today_views')->default(0);
            $table->integer('views')->default(0);
            $table->integer('channel_type')->default(0);
            $table->integer('likes_count')->default(0);
            $table->timestamp('last_watched_at')->nullable();
            $table->timestamps();
            $table->timestamp('blocked_at')->nullable();
            $table->string('block_reason')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
            $table->text('actor_id')->nullable();
            $table->text('key_id')->nullable();
            $table->text('inbox_url')->nullable();
            $table->text('outbox_url')->nullable();
            $table->text('shared_inbox_url')->nullable();
            $table->text('web_url')->nullable();
            $table->timestamp('last_online_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('channels');
    }
};
