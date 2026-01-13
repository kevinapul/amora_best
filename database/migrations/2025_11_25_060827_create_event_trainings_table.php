<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_trainings', function (Blueprint $table) {
    $table->id();

    /* ================= GROUPING ================= */
    $table->foreignId('event_training_group_id')
          ->nullable()
          ->constrained('event_training_groups')
          ->cascadeOnDelete();

    /* ================= CORE TYPE ================= */
    $table->enum('jenis_event', ['training', 'non_training'])
          ->default('training');

    /* ================= TRAINING DETAIL ================= */
    $table->foreignId('training_id')
          ->nullable()
          ->constrained('trainings')
          ->nullOnDelete();

    $table->enum('training_type', ['reguler', 'inhouse'])
          ->nullable();

    /* ================= NON TRAINING DETAIL ================= */
    $table->enum('non_training_type', [
        'perpanjangan',
        'resertifikasi'
    ])->nullable();

    /* ================= EVENT INFO ================= */
    $table->string('job_number')->nullable()->unique();

    $table->date('tanggal_start')->nullable();
    $table->date('tanggal_end')->nullable();

    $table->string('tempat')->nullable();

    /* ================= FINANCIAL ================= */
    $table->decimal('harga_paket', 15, 2)->nullable();

    /* ================= CERTIFICATION ================= */
    $table->string('jenis_sertifikasi')->nullable();
    $table->string('sertifikasi')->nullable();

    /* ================= FLOW ================= */
    $table->enum('status', [
        'pending',
        'active',
        'on_progress',
        'done'
    ])->default('pending');

    /* ================= FINANCE ================= */
    $table->boolean('finance_approved')->default(false);
    $table->timestamp('finance_approved_at')->nullable();

    $table->timestamps();

    /* ================= INDEX ================= */
    $table->index(['jenis_event', 'status']);
    $table->index('non_training_type');
});
    }

    public function down(): void
    {
        Schema::dropIfExists('event_trainings');
    }
};
