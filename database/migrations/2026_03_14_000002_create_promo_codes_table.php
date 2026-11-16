<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('fixed'); // 'percentage' or 'fixed'
            $table->decimal('value', 10, 2);
            $table->string('apply_to')->default('all'); // 'all' or 'specific'
            $table->json('product_ids')->nullable(); // array of product ids if specific
            $table->decimal('min_spend', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
