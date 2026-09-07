<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->string('groom_father')->nullable()->after('groom_nickname');
            $table->string('groom_mother')->nullable()->after('groom_father');
            $table->string('bride_father')->nullable()->after('bride_nickname');
            $table->string('bride_mother')->nullable()->after('bride_father');
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropColumn(['groom_father', 'groom_mother', 'bride_father', 'bride_mother']);
        });
    }
};
