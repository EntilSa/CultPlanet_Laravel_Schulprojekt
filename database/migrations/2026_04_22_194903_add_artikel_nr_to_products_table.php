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
            // artikelnummer direkt nach der id – eindeutig, keine negativen werte
            $table->unsignedInteger('artikel_nr')->unique()->after('id');
        });

        // bestehende produkte bekommen ihre nummer: id + 10000
        foreach (\App\Models\Product::all() as $product) {
            $product->update(['artikel_nr' => 10000 + $product->id]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['artikel_nr']);
            $table->dropColumn('artikel_nr');
        });
    }
};
