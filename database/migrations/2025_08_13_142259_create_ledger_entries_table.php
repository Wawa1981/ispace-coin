<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('transfer_id')->nullable();
            $table->string('direction');
            $table->bigInteger('amount');
            $table->string('ref')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['wallet_id']);
            $table->index(['transaction_id']);
            $table->index(['transfer_id']);
            $table->index(['ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
