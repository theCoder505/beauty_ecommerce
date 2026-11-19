<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_costs', function (Blueprint $table) {
            $table->id();
            $table->date('cost_date')->index();
            $table->string('title');
            $table->string('category')->default('inventory')->index(); // inventory, packaging, shipping_logistics, operational, marketing, other
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedInteger('quantity')->nullable();
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reference_no')->nullable(); // Invoice #, PO #, Receipt #
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index(['cost_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_costs');
    }
};
