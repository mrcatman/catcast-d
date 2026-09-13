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
        // Which streaming server this broadcast went out on. Keys into
        // config/servers.php; see App\Helpers\ServersHelper. Not the legacy
        // `server` column, which held an external service's hostname.
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->string('server_id', 64)->nullable()->index();
        });

        // Everything recorded before the split lived on the single server that
        // is now the default one.
        DB::table('broadcasts')->update(['server_id' => ServersHelper::defaultId()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcasts', function (Blueprint $table) {
            $table->dropIndex(['server_id']);
            $table->dropColumn('server_id');
        });
    }
};
