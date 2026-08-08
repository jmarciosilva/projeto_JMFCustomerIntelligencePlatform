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
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // cross_sell, up_sell, win_back, bundle
            $table->foreignId('contact_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('related_product_id')->nullable();
            $table->decimal('score', 5, 2)->default(0); // 0-100 priority/confidence
            $table->decimal('potential_value', 12, 2)->nullable();
            $table->string('reason');
            $table->timestamp('detected_at')->nullable();
            $table->timestamps();

            $table->index(['application_id', 'type']);
            $table->index('contact_id');
            $table->index('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
