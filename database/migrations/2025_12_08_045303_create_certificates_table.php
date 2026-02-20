<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
    Schema::create('certificates', function (Blueprint $table) {
        $table->id();

        $table->foreignId('participant_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('company_id')
            ->nullable()
            ->constrained('companies')
            ->nullOnDelete();

        $table->foreignId('event_training_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('nomor_sertifikat')->nullable();

        // file pdf
        $table->string('file_path')->nullable();

        $table->date('tanggal_terbit')->nullable();
        $table->date('tanggal_expired')->nullable();
        $table->integer('masa_berlaku_tahun')->nullable();

        $table->enum('status',['active','expiring','expired'])
            ->default('active');

        $table->foreignId('input_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->text('notes')->nullable();

        $table->timestamps();

        $table->unique(['participant_id','event_training_id']);
    });

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
