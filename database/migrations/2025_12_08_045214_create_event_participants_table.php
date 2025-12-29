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
    Schema::create('event_participants', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('event_training_id');
        $table->unsignedBigInteger('participant_id');
        $table->integer('harga_peserta')->default(0);
        $table->timestamps();

        $table->foreign('event_training_id')
            ->references('id')->on('event_trainings')
            ->onDelete('cascade');

        $table->foreign('participant_id')
            ->references('id')->on('participants')
            ->onDelete('cascade');
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
