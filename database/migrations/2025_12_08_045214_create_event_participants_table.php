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

    $table->foreignId('event_training_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('participant_id')
        ->constrained()
        ->cascadeOnDelete();

    // perusahaan saat ikut training
    $table->foreignId('company_id')
        ->nullable()
        ->constrained('companies')
        ->nullOnDelete();

    $table->enum('jenis_layanan',[
        'pelatihan',
        'pelatihan_sertifikasi',
        'sertifikasi_resertifikasi'
    ]);

    $table->decimal('harga_peserta',15,2)->default(0);

    $table->decimal('paid_amount',15,2)->default(0);
    $table->decimal('remaining_amount',15,2)->default(0);

    $table->boolean('is_paid')->default(false);
    $table->timestamp('paid_at')->nullable();

    $table->boolean('certificate_ready')->default(false);
    $table->timestamp('certificate_issued_at')->nullable();

    $table->timestamps();

    $table->unique(['event_training_id','participant_id']);
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
