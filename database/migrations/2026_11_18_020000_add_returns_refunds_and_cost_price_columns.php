<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('return_request_type')->nullable()->after('status'); // 'return' or 'refund'
            $table->text('return_reason')->nullable()->after('return_request_type');
            $table->string('return_status')->nullable()->after('return_reason'); // 'requested', 'approved', 'rejected'
            $table->timestamp('return_requested_at')->nullable()->after('return_status');
            $table->timestamp('return_actioned_at')->nullable()->after('return_requested_at');
            $table->text('return_admin_note')->nullable()->after('return_actioned_at');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('return_admin_note');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 10, 2)->nullable()->default(0)->after('sale_price');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'return_request_type',
                'return_reason',
                'return_status',
                'return_requested_at',
                'return_actioned_at',
                'return_admin_note',
                'refund_amount',
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
