<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_training_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('master_training_id')
                  ->nullable()
                  ->constrained('master_trainings')
                  ->nullOnDelete();

            $table->string('nama_group')->nullable(); 
            // contoh: Rigging Forklift Jan 2026

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_training_groups');
    }
};
