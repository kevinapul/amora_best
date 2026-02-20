<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantsTable extends Migration
{
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
    $table->id();

    $table->string('nik')->nullable()->unique();
    $table->string('nama');
    $table->string('no_hp')->nullable();
    $table->string('alamat')->nullable();
    $table->date('tanggal_lahir')->nullable();
    $table->text('catatan')->nullable();

    // perusahaan terakhir (opsional UI cepat)
    $table->foreignId('last_company_id')
        ->nullable()
        ->constrained('companies')
        ->nullOnDelete();

    $table->timestamps();

    $table->index('nama');
});

    }

    public function down()
    {
        Schema::dropIfExists('participants');
    }
}
