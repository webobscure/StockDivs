<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 24)->index();
            $table->decimal('amount', 20, 6);
            $table->string('currency', 3)->default('USD');
            $table->date('ex_dividend_date')->nullable();
            $table->date('record_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('declaration_date')->nullable();
            $table->decimal('dividend_yield', 10, 4)->nullable();
            $table->string('frequency')->nullable();
            $table->string('provider')->nullable();
            $table->timestamps();

            $table->unique(['ticker', 'ex_dividend_date', 'payment_date', 'provider'], 'dividends_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dividends');
    }
};
