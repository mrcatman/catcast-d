<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropColumn('watch_url');
            $table->dropColumn('viewers');
            $table->string('playback_url')->nullable();
            $table->integer('views')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->string('watch_url')->nullable();
            $table->integer('viewers')->nullable();
            $table->dropColumn('playback_url');
            $table->dropColumn('views');
        });
    }
};
