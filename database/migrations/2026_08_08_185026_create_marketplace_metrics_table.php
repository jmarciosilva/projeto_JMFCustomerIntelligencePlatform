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
        Schema::create('marketplace_metrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->date('date');
            $table->integer('product_views')->default(0);
            $table->integer('product_favorites')->default(0);
            $table->integer('cart_adds')->default(0);
            $table->integer('cart_views')->default(0);
            $table->integer('cart_abandonments')->default(0);
            $table->integer('checkout_starts')->default(0);
            $table->integer('purchases')->default(0);
            $table->integer('reviews')->default(0);
            $table->integer('unique_visitors')->default(0);
            $table->integer('unique_buyers')->default(0);
            $table->decimal('revenue', 12, 2)->default(0);
            $table->decimal('avg_order_value', 12, 2)->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('application_id')->references('id')->on('applications')->cascadeOnDelete();
            $table->unique(['tenant_id', 'application_id', 'seller_id', 'product_id', 'date'], 'unique_marketplace_metrics');
            $table->index(['application_id', 'seller_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_metrics');
    }
};
