<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('collection'); // hero, couple, gallery, video, etc.
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['wedding_id', 'collection', 'sort_order']);
        });

        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Default');
            $table->boolean('is_active')->default(true);
            $table->boolean('autoplay')->default(true);
            $table->boolean('loop')->default(true);
            $table->boolean('shuffle')->default(false);
            $table->integer('volume')->default(50);
            $table->timestamps();

            $table->index(['wedding_id', 'is_active']);
        });

        Schema::create('playlist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playlist_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->nullable();
            $table->integer('duration')->nullable(); // seconds
            $table->integer('start_position')->default(0); // seconds
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['playlist_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlist_items');
        Schema::dropIfExists('playlists');
        Schema::dropIfExists('wedding_media');
    }
};
