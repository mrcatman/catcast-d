<?php

use App\Helpers\ServersHelper;
use App\Models\MediaUploadKey;
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
        Schema::table('media_upload_keys', function (Blueprint $table) {
            // The server the key was issued for. Uploads go straight to the
            // machine that will hold and convert the file, so a key must not be
            // replayable against a different one.
            $table->string('server_id', 64)->nullable()->after('media_id');

            // Largest upload this key may create, from the channel's remaining
            // quota at issue time. tus declares the length up front, so an
            // upload that cannot fit is refused before any bytes move.
            $table->unsignedBigInteger('max_size')->nullable();

            // Bounds how long an unused key stays live. Only checked when an
            // upload starts -- a large file can take hours to transfer, and it
            // was authorised when it began.
            $table->timestamp('expires_at')->nullable();

            // Consumed rather than deleted: tusd retries a failed post-finish
            // hook, and a second delivery has to be recognised as a repeat
            // instead of looking like an unknown key.
            $table->timestamp('used_at')->nullable();

            $table->index('key');
        });

        // Existing keys predate the binding. Attribute them to the default
        // server and expire them on their original schedule, which for anything
        // issued more than an hour ago means immediately.
        DB::table('media_upload_keys')->update([
            'server_id' => ServersHelper::defaultId(),
            'expires_at' => DB::raw('DATE_ADD(created_at, INTERVAL '.MediaUploadKey::LIFETIME_MINUTES.' MINUTE)'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_upload_keys', function (Blueprint $table) {
            $table->dropIndex(['key']);
            $table->dropColumn(['server_id', 'max_size', 'expires_at', 'used_at']);
        });
    }
};
