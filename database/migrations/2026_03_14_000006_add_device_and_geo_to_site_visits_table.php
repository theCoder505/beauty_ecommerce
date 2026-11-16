<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->string('device', 50)->nullable()->after('url');
            $table->string('platform', 50)->nullable()->after('device');
            $table->string('browser', 50)->nullable()->after('platform');
            $table->string('country', 100)->nullable()->after('browser');
            $table->string('country_code', 10)->nullable()->after('country');
            $table->string('city', 100)->nullable()->after('country_code');
            $table->text('user_agent')->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('site_visits', function (Blueprint $table) {
            $table->dropColumn([
                'device',
                'platform',
                'browser',
                'country',
                'country_code',
                'city',
                'user_agent',
            ]);
        });
    }
};
