<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_training_groups', function (Blueprint $table) {
            $table
                ->foreignId('billing_company_id')
                ->nullable()
                ->after('training_type')
                ->constrained('companies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('event_training_groups', function (Blueprint $table) {
            $table->dropForeign(['billing_company_id']);
            $table->dropColumn('billing_company_id');
        });
    }
};
