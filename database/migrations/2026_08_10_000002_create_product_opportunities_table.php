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
        Schema::create('product_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trend_id')->constrained()->cascadeOnDelete();
            $table->foreignId('affiliate_product_id')->constrained('affiliate_products')->cascadeOnDelete();
            $table->decimal('opportunity_score', 5, 2)->default(0);
            $table->json('opportunity_breakdown')->nullable();
            $table->enum('commercial_intent', ['high', 'medium', 'low'])->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'archived'])->default('pending');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['trend_id', 'affiliate_product_id']);
            $table->index('affiliate_product_id');
            $table->index('opportunity_score');
            $table->index('calculated_at');
            $table->index('commercial_intent');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_opportunities');
    }
};
