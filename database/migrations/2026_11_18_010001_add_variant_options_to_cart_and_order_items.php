<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->string('selected_size')->nullable()->after('quantity');
            $table->string('selected_color')->nullable()->after('selected_size');
            $table->string('selected_shade')->nullable()->after('selected_color');
            $table->text('options_data')->nullable()->after('selected_shade');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('selected_size')->nullable()->after('product_sku');
            $table->string('selected_color')->nullable()->after('selected_size');
            $table->string('selected_shade')->nullable()->after('selected_color');
            $table->text('options_data')->nullable()->after('selected_shade');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['selected_size', 'selected_color', 'selected_shade', 'options_data']);
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropColumn(['selected_size', 'selected_color', 'selected_shade', 'options_data']);
        });
    }
};
