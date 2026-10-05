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
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_email')->nullable();
            $table->string('full_name')->nullable();
            $table->string('guest_phone')->nullable();
            $table->enum('fulfillment_type', ['delivery', 'pickup']);
            $table->foreignId('pickup_point_id')->nullable()->constrained()->restrictOnDelete();
            $table->text('delivery_address')->nullable();
            $table->string('status')->default('paid'); // paid | processing | out_for_delivery | ready_for_pickup | received | completed | cancelled
            $table->decimal('total_amount', 10, 2);
            $table->timestamp('received_at')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
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
