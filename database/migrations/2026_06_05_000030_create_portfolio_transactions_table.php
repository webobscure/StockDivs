<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ticker', 24)->index();
            $table->enum('type', ['buy', 'sell']);
            $table->decimal('quantity', 20, 6);
            $table->decimal('price', 20, 6);
            $table->string('currency', 3)->default('USD');
            $table->date('transaction_date');
            $table->decimal('commission', 20, 6)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ticker']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_transactions');
    }
};
