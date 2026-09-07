<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            // Short token: 12 char base62, used in public URL
            // Full invitation_token kept for security validation
            $table->string('short_token', 16)->nullable()->unique()->after('invitation_token');
            $table->index('short_token');
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('short_token');
        });
    }
};
