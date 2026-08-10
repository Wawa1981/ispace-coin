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
        Schema::create('onchain_deposits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('asset', 20);

            $table->decimal('amount', 36, 18)
                ->default(0);

            $table->string('from_address')->nullable();

            $table->string('to_address')->nullable();

            $table->string('txid')->nullable();

            $table->string('status')
                ->default('pending');

            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onchain_deposits');
    }
};
