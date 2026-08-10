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
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Lien vers la table users
            $table->string('currency', 10); // Ex: BTC, ETH, USD
            $table->decimal('amount', 18, 8)->default(0.00000000); // Quantité de crypto/monnaie, avec 8 décimales pour la précision
            $table->timestamps(); // created_at et updated_at

            // Assure qu'un utilisateur n'a qu'une seule balance par devise
            $table->unique(['user_id', 'currency']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balances');
    }
};
