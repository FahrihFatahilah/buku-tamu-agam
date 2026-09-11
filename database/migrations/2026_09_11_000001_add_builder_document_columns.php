<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Additive only: two nullable JSON columns. Existing invitations keep
     * rendering through their coded template until a document is written.
     */
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->json('builder_document')->nullable()->after('settings');
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->json('builder_document')->nullable()->after('animation_personality');
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropColumn('builder_document');
        });

        Schema::table('templates', function (Blueprint $table) {
            $table->dropColumn('builder_document');
        });
    }
};
