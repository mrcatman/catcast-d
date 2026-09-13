<?php

use App\Helpers\ServersHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Which streaming server holds this item's files, and therefore packages
        // its HLS. Uploads always go to the default server for now, but the
        // column is what lets that change without a second migration.
        Schema::table('media', function (Blueprint $table) {
            $table->string('server_id', 64)->nullable()->index();
        });

        DB::table('media')->update(['server_id' => ServersHelper::defaultId()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['server_id']);
            $table->dropColumn('server_id');
        });
    }
};
