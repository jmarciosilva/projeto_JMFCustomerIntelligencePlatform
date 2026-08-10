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
        Schema::create('trend_product_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trend_id')->constrained()->cascadeOnDelete();
            $table->foreignId('affiliate_product_id')->constrained('affiliate_products')->cascadeOnDelete();
            $table->decimal('match_score', 5, 2)->default(0);
            $table->json('match_breakdown')->nullable();
            $table->timestamps();

            $table->unique(['trend_id', 'affiliate_product_id']);
            $table->index('affiliate_product_id');
            $table->index('match_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trend_product_matches');
    }
};
