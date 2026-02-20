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

    $table->foreignId('event_training_group_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->foreignId('training_id')
        ->constrained()
        ->cascadeOnDelete();

    $table->enum('jenis_event',['training','non_training'])->default('training');

    $table->enum('non_training_type',['perpanjangan','resertifikasi'])->nullable();

    $table->date('tanggal_start');
    $table->date('tanggal_end')->nullable();

    $table->enum('status',['pending','active','on_progress','done'])
        ->default('pending');

    $table->boolean('finance_approved')->default(false);
    $table->timestamp('finance_approved_at')->nullable();

    $table->timestamps();

    $table->unique(['event_training_group_id','training_id']);
});

    }

    public function down(): void
    {
        Schema::dropIfExists('event_trainings');
    }
};
