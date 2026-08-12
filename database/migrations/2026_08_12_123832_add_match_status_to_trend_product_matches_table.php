<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('trend_product_matches', function (Blueprint $table) {
            $table->string('match_status')->default('matched')->after('match_breakdown');
        });

        // Backfill: all existing matches are MATCHED
        DB::table('trend_product_matches')
            ->where('match_status', 'matched')
            ->update(['match_status' => 'matched']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trend_product_matches', function (Blueprint $table) {
            $table->dropColumn('match_status');
        });
    }
};
