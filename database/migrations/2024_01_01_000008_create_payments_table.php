<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method'); // cod|manual|stripe
            $table->string('status')->default('pending'); // pending|verified|rejected|refunded
            $table->decimal('amount', 10, 2);
            $table->string('transaction_id')->nullable(); // stripe payment intent or manual tx number
            $table->text('gateway_response')->nullable(); // JSON
            $table->string('verified_by')->nullable(); // admin email
            $table->timestamp('verified_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
            $table->index('order_id');
            $table->index('method');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

