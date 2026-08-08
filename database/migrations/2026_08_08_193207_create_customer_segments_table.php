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
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_id')->constrained()->cascadeOnDelete();
            $table->string('segment'); // vip, engaged, converted, inactive, new
            $table->integer('customer_score')->default(0); // RFV score 0-100
            $table->integer('recency_score')->default(0); // Days since last activity
            $table->integer('frequency_score')->default(0); // Purchase count
            $table->integer('monetary_score')->default(0); // Total value
            $table->timestamp('segmented_at')->nullable();
            $table->timestamps();

            $table->unique(['contact_id', 'segment']);
            $table->index('segment');
            $table->index('customer_score');
            $table->index('segmented_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_segments');
    }
};
