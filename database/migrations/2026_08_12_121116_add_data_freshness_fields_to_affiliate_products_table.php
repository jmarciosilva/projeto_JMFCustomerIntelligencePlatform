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
        Schema::table('affiliate_products', function (Blueprint $table) {
            $table->timestamp('price_checked_at')->nullable()->after('last_checked_at');
            $table->timestamp('availability_checked_at')->nullable()->after('price_checked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('affiliate_products', function (Blueprint $table) {
            $table->dropColumn(['price_checked_at', 'availability_checked_at']);
        });
    }
};
