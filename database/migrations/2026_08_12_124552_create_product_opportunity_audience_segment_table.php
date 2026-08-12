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
        Schema::create('product_opportunity_audience_segment', function (Blueprint $table) {
            $table->unsignedBigInteger('product_opportunity_id');
            $table->unsignedBigInteger('audience_segment_id');
            $table->timestamps();
            $table->primary(['product_opportunity_id', 'audience_segment_id']);
            $table->foreign('product_opportunity_id', 'po_aud_seg_po_fk')
                ->references('id')->on('product_opportunities')
                ->onDelete('cascade');
            $table->foreign('audience_segment_id', 'po_aud_seg_aud_fk')
                ->references('id')->on('audience_segments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_opportunity_audience_segment');
    }
};
