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
    Schema::create('trainings', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();   // contoh: ROF
        $table->string('name');             // contoh: Rigged & Operation Forklift
        $table->text('description')->nullable(); // optional
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
