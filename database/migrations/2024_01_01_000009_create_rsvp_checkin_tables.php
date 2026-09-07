<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rsvps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->enum('attendance_status', ['attending', 'not_attending', 'maybe', 'pending'])->default('pending');
            $table->integer('pax')->default(1);
            $table->text('note')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->unique('guest_id');
            $table->index(['wedding_id', 'attendance_status']);
        });

        Schema::create('guest_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained()->cascadeOnDelete();
            $table->timestamp('checked_in_at');
            $table->integer('pax')->default(1);
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('guest_id');
            $table->index(['wedding_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_checkins');
        Schema::dropIfExists('rsvps');
    }
};
