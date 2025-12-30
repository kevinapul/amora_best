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
        Schema::table('certificates', function (Blueprint $table) {
            $table->unsignedTinyInteger('masa_berlaku_tahun')->nullable()->after('tanggal_expired');
            $table->enum('status', ['active', 'expiring', 'expired'])
                  ->default('active')
                  ->after('masa_berlaku_tahun');
            $table->unsignedBigInteger('input_by')->nullable()->after('status');
            $table->text('notes')->nullable()->after('input_by');

            $table->unique(['participant_id', 'event_training_id'], 'cert_participant_event_unique');
    });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            //
        });
    }
};
