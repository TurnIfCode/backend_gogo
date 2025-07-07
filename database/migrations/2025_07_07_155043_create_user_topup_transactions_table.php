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
        Schema::create('user_topup_transactions', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('wallet_id', 50);
            $table->decimal('coin_amount', total: 12, places: 2);
            $table->decimal('price', total: 12, places: 2);
            $table->enum('status', ['Proses', 'Selesai', 'Batal'])->default('Proses');
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_topup_transactions', function (Blueprint $table) {
            $table->dropForeign(['wallet_id']);
        });
        Schema::dropIfExists('user_topup_transactions');
    }
};
