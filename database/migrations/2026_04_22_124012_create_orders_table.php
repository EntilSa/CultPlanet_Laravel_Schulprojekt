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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // lieferadresse wird direkt gespeichert (kein eigenes adress-model nötig)
            $table->string('vorname');
            $table->string('nachname');
            $table->string('strasse');
            $table->string('plz', 10);
            $table->string('ort');
            $table->string('zahlungsmethode'); // z.b. 'paypal' oder 'sofortüberweisung'
            $table->decimal('total', 8, 2); // gesamtpreis der bestellung
            $table->string('status')->default('offen'); // offen, bezahlt, versendet, storniert
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
