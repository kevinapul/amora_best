<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_trainings', function (Blueprint $table) {
            $table->id();

            /* ================= CORE TYPE ================= */

            // TRAINING / NON TRAINING
            $table->enum('jenis_event', [
                'training',
                'non_training'
            ])->default('training');

            // DETAIL TRAINING
            $table->enum('training_type', [
                'reguler',
                'inhouse'
            ])->nullable();

            // DETAIL NON TRAINING
            $table->enum('non_training_type', [
                'perpanjangan',
                'resertifikasi'
            ])->nullable();

            /* ================= RELATION ================= */

            // null untuk non training
            $table->foreignId('training_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /* ================= EVENT INFO ================= */

            $table->integer('harga_paket')->nullable();

            $table->string('job_number')
                ->nullable()
                ->unique();

            $table->date('tanggal_start')->nullable();
            $table->date('tanggal_end')->nullable();

            $table->string('tempat')->nullable();

            /* ================= SERTIFIKASI ================= */

            $table->string('jenis_sertifikasi')->nullable();
            $table->string('sertifikasi')->nullable();

            /* ================= FLOW STATUS ================= */

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
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_trainings');
    }
};
