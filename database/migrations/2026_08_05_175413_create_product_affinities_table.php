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
        Schema::create('product_affinities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('subject_type');
            $table->string('subject_id_a');
            $table->string('subject_id_b');
            $table->unsignedInteger('co_occurrences')->default(0);
            $table->timestamp('computed_at');
            $table->timestamps();

            $table->unique(
                ['application_id', 'subject_type', 'subject_id_a', 'subject_id_b'],
                'product_affinities_unique_pair'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_affinities');
    }
};
