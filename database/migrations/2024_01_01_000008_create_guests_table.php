<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'sort_order']);
        });

        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('guest_categories')->nullOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('invitation_token', 64)->unique();
            $table->integer('max_pax')->default(1);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'sent', 'opened'])->default('pending');
            $table->timestamp('token_generated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['wedding_id', 'category_id']);
            $table->index(['wedding_id', 'status']);
            $table->index('invitation_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guests');
        Schema::dropIfExists('guest_categories');
    }
};
