<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
Schema::create('invoices', function (Blueprint $table) {
    $table->id();

    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('master_training_id')->constrained()->cascadeOnDelete();

    $table->string('invoice_number')->unique();

    $table->decimal('total_amount',15,2)->default(0);
    $table->decimal('paid_amount',15,2)->default(0);

    $table->enum('status',['draft','sent','partial','paid','cancelled'])
        ->default('draft');

    $table->date('issued_at')->nullable();
    $table->date('due_at')->nullable();

    $table->text('notes')->nullable();
    $table->text('footer_text')->nullable();

    $table->timestamps();
});



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
