<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            // Short public ID: 6 alphanumeric chars, e.g. "a3f9b2"
            $table->string('short_id', 8)->nullable()->unique()->after('public_id');
            $table->index('short_id');
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropColumn('short_id');
        });
    }
};
