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
        Schema::table('trends', function (Blueprint $table) {
            $table->decimal('trend_score', 5, 2)->nullable()->after('status');
            $table->json('trend_score_breakdown')->nullable()->after('trend_score');
            $table->timestamp('trend_score_computed_at')->nullable()->after('trend_score_breakdown');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trends', function (Blueprint $table) {
            $table->dropColumn(['trend_score', 'trend_score_breakdown', 'trend_score_computed_at']);
        });
    }
};
