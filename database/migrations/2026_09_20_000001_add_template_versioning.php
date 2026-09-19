<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. templates already has status + current_version from base migration
        // Nothing to alter on templates table.

        // 2. Template versions — immutable once published
        Schema::create('template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->json('document')->nullable();          // builder document snapshot
            $table->json('default_settings')->nullable();  // palette, fonts
            $table->json('default_sections')->nullable();  // section layout
            $table->json('animation_personality')->nullable();
            $table->text('changelog')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['template_id', 'version']);
            $table->index(['template_id', 'status']);
        });

        // 3. Invitation template snapshots — frozen copy per wedding
        Schema::create('template_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_version_id')->constrained('template_versions')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('document')->nullable();          // frozen builder document
            $table->json('default_settings')->nullable();  // frozen palette/fonts
            $table->json('default_sections')->nullable();  // frozen section layout
            $table->json('animation_personality')->nullable();
            $table->timestamps();

            $table->index('wedding_id');
            $table->index('template_version_id');
        });

        // 4. Add snapshot reference to weddings
        Schema::table('weddings', function (Blueprint $table) {
            $table->foreignId('template_snapshot_id')
                ->nullable()
                ->after('template_id')
                ->constrained('template_snapshots')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('weddings', function (Blueprint $table) {
            $table->dropForeign(['template_snapshot_id']);
            $table->dropColumn('template_snapshot_id');
        });

        Schema::dropIfExists('template_snapshots');
        Schema::dropIfExists('template_versions');

        // templates columns were added in base migration
    }
};
