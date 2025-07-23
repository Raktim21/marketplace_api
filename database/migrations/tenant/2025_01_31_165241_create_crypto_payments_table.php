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
        Schema::create('crypto_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('payment_id');
            $table->string('payment_status');
            $table->string('pay_address');
            $table->float('price_amount');
            $table->string('price_currency');
            $table->float('pay_amount');
            $table->float('amount_received');
            $table->string('pay_currency');
            $table->string('purchase_id');
            $table->string('network');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_payments');
    }
};
