<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();

            /* ================= RELATION ================= */
            $table->foreignId('event_training_id')
                  ->constrained('event_trainings')
                  ->cascadeOnDelete();

            $table->foreignId('participant_id')
                  ->constrained('participants')
                  ->cascadeOnDelete();

            /* ================= JENIS LAYANAN =================
               - pelatihan
               - pelatihan_sertifikasi
               - sertifikasi_resertifikasi
            */
            $table->enum('jenis_layanan', [
                'pelatihan',
                'pelatihan_sertifikasi',
                'sertifikasi_resertifikasi',
            ]);

            /* ================= FINANCE ================= */
            $table->integer('harga_peserta')->default(0);

            $table->boolean('is_paid')
                  ->default(false);

            $table->timestamp('paid_at')
                  ->nullable();

            /* ================= CERTIFICATE ================= */
            $table->boolean('certificate_ready')
                  ->default(false);

            $table->timestamp('certificate_issued_at')
                  ->nullable();

            $table->timestamps();

            /* ================= CONSTRAINT ================= */
            $table->unique(
                ['event_training_id', 'participant_id'],
                'event_participant_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
