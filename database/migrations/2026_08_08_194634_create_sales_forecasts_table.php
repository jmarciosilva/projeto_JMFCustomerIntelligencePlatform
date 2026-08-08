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
        Schema::create('sales_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->date('forecast_date');
            $table->integer('horizon_days'); // 7 or 30
            $table->decimal('predicted_revenue', 12, 2)->default(0);
            $table->integer('predicted_purchases')->default(0);
            $table->string('confidence'); // low, medium, high
            $table->string('method')->default('moving_average');
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'seller_id', 'forecast_date', 'horizon_days'], 'unique_sales_forecast');
            $table->index(['application_id', 'forecast_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_forecasts');
    }
};
