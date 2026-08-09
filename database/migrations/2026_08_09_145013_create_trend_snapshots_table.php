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
        Schema::create('trend_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trend_id')->constrained()->cascadeOnDelete();
            $table->string('source');
            $table->decimal('score', 5, 2)->nullable();
            $table->unsignedInteger('mentions')->nullable();
            $table->unsignedInteger('engagement')->nullable();
            $table->decimal('velocity', 8, 2)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('collected_at');
            $table->timestamps();

            $table->index(['trend_id', 'collected_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trend_snapshots');
    }
};
