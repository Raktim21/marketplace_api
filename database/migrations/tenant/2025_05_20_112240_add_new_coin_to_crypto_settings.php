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
        Schema::table('crypto_settings', function (Blueprint $table) {
            $table->boolean('solana_status')->default(false);
            $table->boolean('eth_status')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crypto_settings', function (Blueprint $table) {
            $table->dropColumn('solana_status');
            $table->dropColumn('eth_status');
        });
    }
};
