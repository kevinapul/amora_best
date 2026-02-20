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
        ->constrained()
        ->cascadeOnDelete();

    $table->string('nama_group')->nullable();
    $table->string('job_number')->nullable()->unique();

    $table->enum('training_type', ['reguler','inhouse']);

    $table->foreignId('billing_company_id')
        ->nullable()
        ->constrained('companies')
        ->nullOnDelete();

    $table->decimal('harga_paket',15,2)->nullable();

    $table->decimal('paid_amount',15,2)->default(0);

    $table->boolean('finance_approved')->default(false);
    $table->timestamp('finance_approved_at')->nullable();

    $table->string('tempat')->nullable();
    $table->string('jenis_sertifikasi')->nullable();
    $table->string('sertifikasi')->nullable();

    $table->timestamps();
});

    }

    public function down(): void
    {
        Schema::dropIfExists('event_training_groups');
    }
};
