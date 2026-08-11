<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adicionar colunas de identidade e rastreamento a affiliate_conversions.
     * Ordem crítica: adicionar como nullable, depois fazer backfill, depois criar constraints.
     */
    public function up(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            // Provider: identificador do provedor da conversão (magalu, amazon, manual, etc)
            if (!Schema::hasColumn('affiliate_conversions', 'provider')) {
                $table->string('provider')
                    ->nullable()
                    ->after('application_id')
                    ->comment('Provedor da conversão (magalu, amazon, manual, etc)');
            }

            // External Conversion ID: ID da conversão no provider externo
            if (!Schema::hasColumn('affiliate_conversions', 'external_conversion_id')) {
                $table->string('external_conversion_id')
                    ->nullable()
                    ->after('provider')
                    ->comment('ID externo da conversão no provider (obrigatório para providers externos)');
            }

            // Product Opportunity ID: rastreamento end-to-end
            if (!Schema::hasColumn('affiliate_conversions', 'product_opportunity_id')) {
                $table->unsignedBigInteger('product_opportunity_id')
                    ->nullable()
                    ->after('external_conversion_id')
                    ->comment('ID da oportunidade que gerou esta conversão (quando rastreável)');

                $table->foreign('product_opportunity_id')
                    ->references('id')
                    ->on('product_opportunities')
                    ->onDelete('set null');
            }

            // Índices para queries comuns
            if (!Schema::hasIndex('affiliate_conversions', 'idx_provider_ext_id')) {
                $table->index(['provider', 'external_conversion_id'], 'idx_provider_ext_id');
            }

            if (!Schema::hasIndex('affiliate_conversions', 'idx_product_opportunity_id')) {
                $table->index('product_opportunity_id', 'idx_product_opportunity_id');
            }

            if (!Schema::hasIndex('affiliate_conversions', 'idx_provider_created')) {
                $table->index(['provider', 'created_at'], 'idx_provider_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            if (Schema::hasColumn('affiliate_conversions', 'product_opportunity_id')) {
                $table->dropForeign(['product_opportunity_id']);
            }

            $table->dropIndexIfExists('idx_provider_ext_id');
            $table->dropIndexIfExists('idx_product_opportunity_id');
            $table->dropIndexIfExists('idx_provider_created');

            $columns = ['provider', 'external_conversion_id', 'product_opportunity_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('affiliate_conversions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
