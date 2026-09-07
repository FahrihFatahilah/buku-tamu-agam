<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('shared'); // shared, dedicated, enterprise
            $table->enum('status', ['pending', 'provisioning', 'deploying', 'healthy', 'failed', 'suspended', 'terminated'])->default('pending');
            $table->string('host')->nullable();
            $table->json('config')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();

            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
