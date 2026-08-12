<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adicionar product_opportunity_id a affiliate_links.
     * Isso permite rastrear qual oportunidade originou cada link.
     */
    public function up(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliate_links', 'product_opportunity_id')) {
                $table->unsignedBigInteger('product_opportunity_id')
                    ->nullable()
                    ->after('affiliate_product_id')
                    ->comment('ID da oportunidade que gerou este link');

                $table->foreign('product_opportunity_id')
                    ->references('id')
                    ->on('product_opportunities')
                    ->onDelete('set null');

                $table->index('product_opportunity_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_links', function (Blueprint $table) {
            if (Schema::hasColumn('affiliate_links', 'product_opportunity_id')) {
                $table->dropForeign(['product_opportunity_id']);
                $table->dropIndex(['product_opportunity_id']);
                $table->dropColumn('product_opportunity_id');
            }
        });
    }
};
