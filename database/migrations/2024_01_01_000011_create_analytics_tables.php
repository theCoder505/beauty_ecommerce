<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->date('viewed_date');
            $table->timestamps();
            $table->unique(['product_id', 'session_id', 'viewed_date']);
            $table->index(['product_id', 'viewed_date']);
        });

        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('url', 500)->nullable();
            $table->date('visited_date');
            $table->timestamps();
            $table->unique(['session_id', 'visited_date']);
            $table->index('visited_date');
        });

        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('site_visits');
        Schema::dropIfExists('product_views');
    }
};

