<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->enum('type', ['subdomain', 'custom'])->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->enum('verification_status', ['pending', 'verified', 'failed'])->default('pending');
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->enum('ssl_status', ['pending', 'active', 'failed', 'none'])->default('none');
            $table->timestamps();

            $table->index(['domain', 'is_active']);
            $table->index(['wedding_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_domains');
    }
};
