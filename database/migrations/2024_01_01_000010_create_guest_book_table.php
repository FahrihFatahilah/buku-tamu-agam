<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_book_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guest_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('message');
            $table->enum('attendance_status', ['attending', 'not_attending', 'maybe'])->nullable();
            $table->integer('pax')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'hidden'])->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['wedding_id', 'status']);
            $table->index(['wedding_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_book_entries');
    }
};
