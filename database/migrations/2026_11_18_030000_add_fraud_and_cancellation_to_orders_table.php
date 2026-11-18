<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('notes');
            $table->string('cancelled_by')->nullable()->after('cancellation_reason');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->integer('fraud_risk_score')->nullable()->after('cancelled_at');
            $table->string('fraud_risk_level')->nullable()->default('low')->after('fraud_risk_score');
            $table->json('fraud_flags')->nullable()->after('fraud_risk_level');
            $table->index('fraud_risk_level');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['fraud_risk_level']);
            $table->dropColumn([
                'cancellation_reason',
                'cancelled_by',
                'cancelled_at',
                'fraud_risk_score',
                'fraud_risk_level',
                'fraud_flags',
            ]);
        });
    }
};
