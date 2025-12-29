<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('event_participants', function (Blueprint $table) {

            // 💰 STATUS PEMBAYARAN PER PESERTA
            $table->boolean('is_paid')
                  ->default(false)
                  ->after('harga_peserta');

            $table->timestamp('paid_at')
                  ->nullable()
                  ->after('is_paid');

            // 🎓 STATUS SERTIFIKAT
            $table->boolean('certificate_ready')
                  ->default(false)
                  ->after('paid_at');

            $table->timestamp('certificate_issued_at')
                  ->nullable()
                  ->after('certificate_ready');
        });
    }

    public function down()
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn([
                'is_paid',
                'paid_at',
                'certificate_ready',
                'certificate_issued_at'
            ]);
        });
    }
};
