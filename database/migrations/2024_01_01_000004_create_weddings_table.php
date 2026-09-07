<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained()->nullOnDelete();
            $table->string('public_id', 20)->unique();
            $table->string('slug');
            $table->string('title');
            $table->string('groom_name');
            $table->string('bride_name');
            $table->string('groom_nickname')->nullable();
            $table->string('bride_nickname')->nullable();
            $table->text('description')->nullable();
            $table->text('quote')->nullable();
            $table->date('date')->nullable();
            $table->string('venue')->nullable();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('maps_url')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('favicon')->nullable();
            $table->json('appearance')->nullable();
            $table->json('animation_config')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('public_id');
            $table->index(['client_id', 'status']);
            $table->index('status');
            $table->index('slug');
        });

        // Slug history for canonical redirects
        Schema::create('wedding_slug_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('slug');
            $table->timestamp('retired_at');

            $table->index(['slug', 'wedding_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_slug_history');
        Schema::dropIfExists('weddings');
    }
};
