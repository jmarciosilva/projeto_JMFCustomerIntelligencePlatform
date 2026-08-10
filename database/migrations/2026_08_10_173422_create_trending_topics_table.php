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
        Schema::create('trending_topics', function (Blueprint $table) {
            $table->id();
            $table->string('topic')->unique();
            $table->text('description')->nullable();
            $table->integer('search_volume')->default(0);
            $table->integer('growth_percentage')->nullable();
            $table->enum('category', ['Technology', 'Business', 'Entertainment', 'Sports', 'Health', 'Fashion', 'Food', 'Travel', 'Other'])->default('Other');
            $table->enum('region', ['BR', 'US', 'GLOBAL'])->default('BR');
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
            $table->index('category');
            $table->index('region');
            $table->index('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trending_topics');
    }
};
