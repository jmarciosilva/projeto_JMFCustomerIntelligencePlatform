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
        Schema::create('product_performance_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained();
            $table->foreignId('affiliate_product_id')->constrained();
            $table->integer('total_clicks')->default(0);
            $table->integer('total_conversions')->default(0);
            $table->integer('total_sales')->default(0);
            $table->decimal('total_commission', 12, 2)->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('ctr_score', 5, 2)->default(0);
            $table->decimal('conversion_score', 5, 2)->default(0);
            $table->decimal('sales_score', 5, 2)->default(0);
            $table->decimal('recurrence_score', 5, 2)->default(0);
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            $table->unique(['application_id', 'affiliate_product_id'], 'pps_app_prod_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_performance_scores');
    }
};
