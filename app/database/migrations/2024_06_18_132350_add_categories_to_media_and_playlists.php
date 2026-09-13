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
        Schema::rename('broadcast_categories', 'categories');
        Schema::table('media', function (Blueprint $table) {
            $table->integer('category_id')->nullable();
        });
        Schema::table('playlists', function (Blueprint $table) {
            $table->integer('category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('categories', 'broadcast_categories');
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
        Schema::table('playlists', function (Blueprint $table) {
            $table->dropColumn('category_id');
        });
    }
};
