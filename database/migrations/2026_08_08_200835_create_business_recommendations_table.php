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
        Schema::create('business_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('seller_id');
            $table->string('type'); // sales_drop, kit_opportunity, price_outlier, ideal_timing
            $table->decimal('priority', 5, 2)->default(0); // 0-100, expected impact
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // supporting metrics
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'seller_id']);
            $table->index('type');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_recommendations');
    }
};
