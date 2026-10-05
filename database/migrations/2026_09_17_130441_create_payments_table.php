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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('gateway')->default('paystack'); // paystack, cash, bank_transfer
            $table->string('reference')->unique(); // paystack ref, or your own generated ref for cash/pod
            $table->string('status')->default('pending'); // pending, success, failed, abandoned, refunded
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('NGN');
            $table->string('channel')->nullable(); // card, bank, ussd, pos, cash
            $table->json('gateway_response')->nullable(); // raw Paystack payload for debugging/audit
            $table->timestamp('paid_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
