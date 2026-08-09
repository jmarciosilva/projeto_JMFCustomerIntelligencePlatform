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
        Schema::create('affiliate_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('provider')->default('manual');
            $table->string('status')->default('active');
            $table->json('configuration')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_programs');
    }
};
