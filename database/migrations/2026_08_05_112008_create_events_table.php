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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $table->foreignId('application_id')->constrained()->restrictOnDelete();
            $table->string('event_id');
            $table->string('event_name');
            $table->string('visitor_id');
            $table->string('session_id')->nullable();
            $table->string('contact_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->json('properties')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('received_at');
            $table->timestamps();

            $table->unique(['application_id', 'event_id']);
            $table->index(['tenant_id', 'application_id', 'event_name']);
            $table->index(['application_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
