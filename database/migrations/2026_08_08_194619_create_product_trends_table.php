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
        Schema::create('product_trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->string('direction'); // rising, falling, stable
            $table->decimal('growth_rate', 8, 2)->default(0); // % change vs previous period
            $table->integer('current_views')->default(0);
            $table->integer('current_purchases')->default(0);
            $table->decimal('current_revenue', 12, 2)->default(0);
            $table->integer('previous_views')->default(0);
            $table->integer('previous_purchases')->default(0);
            $table->decimal('previous_revenue', 12, 2)->default(0);
            $table->timestamp('computed_at')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'product_id']);
            $table->index('direction');
            $table->index('growth_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_trends');
    }
};
