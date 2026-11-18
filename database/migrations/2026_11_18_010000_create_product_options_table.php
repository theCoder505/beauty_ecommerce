<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type'); // 'size', 'color', 'shade'
            $table->string('name'); // e.g. "50ml", "Ruby Red", "Shade 101 Ivory"
            $table->string('code')->nullable(); // hex color e.g. "#e11d48" or shade code
            $table->decimal('price_modifier', 10, 2)->default(0.00);
            $table->boolean('is_available')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['product_id', 'type']);
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_options');
    }
};
