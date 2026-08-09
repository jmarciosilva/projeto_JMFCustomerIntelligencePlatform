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
        Schema::create('trends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->foreignId('watchlist_id')->constrained()->restrictOnDelete();
            $table->string('term');
            $table->string('type')->default('keyword');
            $table->string('status')->default('active');
            $table->timestamp('last_collected_at')->nullable();
            $table->timestamps();

            $table->unique(['watchlist_id', 'term']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trends');
    }
};
