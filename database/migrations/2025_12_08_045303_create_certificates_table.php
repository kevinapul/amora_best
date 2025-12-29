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
        $table->unsignedBigInteger('participant_id');
        $table->unsignedBigInteger('event_training_id');
        $table->string('nomor_sertifikat')->nullable();
        $table->date('tanggal_terbit')->nullable();
        $table->date('tanggal_expired')->nullable();
        $table->timestamps();

        $table->foreign('participant_id')
            ->references('id')->on('participants')
            ->onDelete('cascade');

        $table->foreign('event_training_id')
            ->references('id')->on('event_trainings')
            ->onDelete('cascade');
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
