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
        Schema::table('banner_settings', function (Blueprint $table) {
            $table->string('image')->nullable()->change();
            $table->text('subtitle')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banner_settings', function (Blueprint $table) {
            $table->string('image')->change();
            $table->string('subtitle')->change();
        });
    }
};
