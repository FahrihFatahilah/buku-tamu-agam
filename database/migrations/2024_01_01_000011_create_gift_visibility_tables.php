<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['bank_transfer', 'qris', 'e_wallet', 'cash', 'custom']);
            $table->string('label');
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('merchant_name')->nullable();
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['wedding_id', 'is_active', 'sort_order']);
        });

        // Generic visibility rules engine
        Schema::create('visibility_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('entity_type'); // gift_method, section, event, etc.
            $table->unsignedBigInteger('entity_id');
            $table->string('scope'); // wedding_default, category, guest
            $table->unsignedBigInteger('scope_id')->nullable(); // category_id or guest_id
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['wedding_id', 'entity_type', 'entity_id']);
            $table->index(['scope', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visibility_rules');
        Schema::dropIfExists('gift_methods');
    }
};
