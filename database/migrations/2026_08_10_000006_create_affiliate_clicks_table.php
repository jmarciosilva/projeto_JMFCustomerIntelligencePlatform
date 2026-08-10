<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_link_id')->constrained()->cascadeOnDelete();
            $table->string('visitor_id')->nullable();
            $table->string('source')->nullable();
            $table->string('medium')->nullable();
            $table->text('referer')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_hash')->nullable();
            $table->timestamp('clicked_at');
            $table->timestamps();

            $table->index('affiliate_link_id');
            $table->index('clicked_at');
            $table->index('visitor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_clicks');
    }
};
