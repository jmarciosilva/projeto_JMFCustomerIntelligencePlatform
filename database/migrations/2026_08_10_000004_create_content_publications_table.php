<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_opportunity_id')->nullable()->constrained('product_opportunities')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('content_type', ['blog_post', 'social_media', 'email', 'video', 'other'])->default('other');
            $table->enum('platform', ['instagram', 'facebook', 'whatsapp', 'email', 'website', 'other'])->default('website');
            $table->timestamp('published_at')->nullable();
            $table->text('url')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();

            $table->index('campaign_id');
            $table->index('status');
            $table->index('platform');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_publications');
    }
};
