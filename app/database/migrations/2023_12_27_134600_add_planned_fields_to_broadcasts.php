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
            $table->dateTime('started_at')->nullable();
            $table->dateTime('will_start_at')->nullable();
            $table->dateTime('will_end_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropColumn('started_at');
            $table->dropColumn('will_start_at');
            $table->dropColumn('will_end_at');
        });
    }
};
