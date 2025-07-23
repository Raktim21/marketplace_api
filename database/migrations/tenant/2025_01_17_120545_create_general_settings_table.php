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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo');
            $table->string('favicon');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            $table->boolean('service_section')->default(true);
            $table->boolean('product_section')->default(true);
            $table->boolean('faq_section')->default(true);
            $table->string('discord_link')->nullable();
            $table->string('telegram_link')->nullable();
            $table->string('tiktok_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->longText('seller_text')->nullable();
            $table->boolean('related_product_status')->default(true);
            $table->boolean('store_status')->default(true);
            $table->string('primary_color')->nullable();
            $table->string('hover_color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};
