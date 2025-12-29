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
            $table->string('nama');
            $table->string('perusahaan')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('alamat')->nullable();
            $table->string('nik')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('participants');
    }
}
