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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('start_price', 8, 2);
            $table->datetime('start_time');
            $table->datetime('end_time');
            // gewinner wird erst nach auktionsende gesetzt
            $table->foreignId('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('winning_bid', 8, 2)->nullable();
            // geplant = noch nicht gestartet, aktiv = läuft, beendet = abgeschlossen
            $table->enum('status', ['geplant', 'aktiv', 'beendet'])->default('geplant');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
