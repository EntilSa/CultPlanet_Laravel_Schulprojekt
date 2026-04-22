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
        Schema::table('products', function (Blueprint $table) {
            // nullable damit das model die nummer nach dem create() setzen kann
            // unique() weglassen – index existiert bereits aus vorheriger migration
            $table->unsignedInteger('artikel_nr')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('artikel_nr')->nullable(false)->change();
        });
    }
};
