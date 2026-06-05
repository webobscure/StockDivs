<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('ticker', 24)->index();
            $table->decimal('price', 20, 6);
            $table->string('currency', 3)->default('USD');
            $table->decimal('change', 20, 6)->default(0);
            $table->decimal('change_percent', 10, 4)->default(0);
            $table->timestamp('market_time')->nullable();
            $table->string('provider')->nullable();
            $table->timestamps();

            $table->unique(['ticker', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_quotes');
    }
};
