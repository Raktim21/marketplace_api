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
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('service_section')->default(false)->change();
            $table->boolean('faq_section')->default(false)->change();
            $table->boolean('most_sold_products')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('service_section')->default(true)->change();
            $table->boolean('faq_section')->default(true)->change();
            $table->boolean('most_sold_products')->default(true)->change();
        });
    }
};
