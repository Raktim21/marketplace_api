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
        Schema::dropIfExists('email_settings');
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_order_delivery')->default(true);
            $table->boolean('is_product_out_of_stock')->default(true);
            $table->boolean('is_product_restock')->default(true);
            $table->text('discord_webhook')->nullable();
            $table->boolean('is_email_notification')->default(true);
            $table->boolean('is_discord_notification')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
