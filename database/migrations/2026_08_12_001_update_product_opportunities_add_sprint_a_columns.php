<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adicionar colunas necessárias para Sprint A.
     * Tabela já existe (criada em migração anterior).
     */
    public function up(): void
    {
        Schema::table('product_opportunities', function (Blueprint $table) {
            // Status workflow para curadoria
            if (! Schema::hasColumn('product_opportunities', 'status_sprint_a')) {
                $table->enum('status_sprint_a', ['DISCOVERED', 'ANALYZING', 'APPROVED', 'REJECTED', 'PUBLISHED', 'EXPIRED'])
                    ->default('DISCOVERED')
                    ->after('id')
                    ->comment('Status do workflow de curadoria (Sprint A)');
            }

            // Scores Sprint A
            if (! Schema::hasColumn('product_opportunities', 'discovery_opportunity_score')) {
                $table->integer('discovery_opportunity_score')
                    ->nullable()
                    ->after('status_sprint_a')
                    ->comment('Score de oportunidade calculado no momento da descoberta (nunca recalculado)');
            }

            if (! Schema::hasColumn('product_opportunities', 'opportunity_score_breakdown')) {
                $table->json('opportunity_score_breakdown')
                    ->nullable()
                    ->after('discovery_opportunity_score')
                    ->comment('Breakdown do opportunity score com fatores ponderados');
            }

            if (! Schema::hasColumn('product_opportunities', 'purchase_intent_score')) {
                $table->integer('purchase_intent_score')
                    ->nullable()
                    ->after('opportunity_score_breakdown')
                    ->comment('Score de intenção de compra (0-100)');
            }

            if (! Schema::hasColumn('product_opportunities', 'purchase_intent_label')) {
                $table->enum('purchase_intent_label', ['LOW', 'MEDIUM', 'HIGH'])
                    ->nullable()
                    ->after('purchase_intent_score')
                    ->comment('Label derivado do purchase_intent_score');
            }

            if (! Schema::hasColumn('product_opportunities', 'purchase_intent_breakdown')) {
                $table->json('purchase_intent_breakdown')
                    ->nullable()
                    ->after('purchase_intent_label')
                    ->comment('Breakdown explicável da intenção de compra');
            }

            if (! Schema::hasColumn('product_opportunities', 'actual_performance_score')) {
                $table->integer('actual_performance_score')
                    ->nullable()
                    ->after('purchase_intent_breakdown')
                    ->comment('Performance score real (calculado após cliques/conversões, Sprint B)');
            }

            if (! Schema::hasColumn('product_opportunities', 'performance_score_breakdown')) {
                $table->json('performance_score_breakdown')
                    ->nullable()
                    ->after('actual_performance_score')
                    ->comment('Breakdown do performance score');
            }

            // Timestamps de ciclo de vida
            if (! Schema::hasColumn('product_opportunities', 'approved_at')) {
                $table->timestamp('approved_at')
                    ->nullable()
                    ->after('performance_score_breakdown')
                    ->comment('Data/hora de aprovação');
            }

            if (! Schema::hasColumn('product_opportunities', 'published_at')) {
                $table->timestamp('published_at')
                    ->nullable()
                    ->after('approved_at')
                    ->comment('Data/hora de publicação');
            }

            if (! Schema::hasColumn('product_opportunities', 'expired_at')) {
                $table->timestamp('expired_at')
                    ->nullable()
                    ->after('published_at')
                    ->comment('Data/hora de expiração');
            }

            if (! Schema::hasColumn('product_opportunities', 'expires_at')) {
                $table->timestamp('expires_at')
                    ->nullable()
                    ->after('expired_at')
                    ->comment('Data/hora prevista para expiração (TTL de curadoria)');
            }

            if (! Schema::hasColumn('product_opportunities', 'expiration_reason')) {
                $table->string('expiration_reason')
                    ->nullable()
                    ->after('expires_at')
                    ->comment('Motivo da expiração (e.g., ttl_exceeded)');
            }

            // Índices para queries comuns
            if (! Schema::hasIndex('product_opportunities', 'idx_status_sprint_a_expires')) {
                $table->index(['status_sprint_a', 'expires_at'], 'idx_status_sprint_a_expires');
            }

            if (! Schema::hasIndex('product_opportunities', 'idx_app_status_sprint_a')) {
                $table->index(['application_id', 'status_sprint_a'], 'idx_app_status_sprint_a');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_opportunities', function (Blueprint $table) {
            // Remover índices
            $table->dropIndexIfExists('idx_status_sprint_a_expires');
            $table->dropIndexIfExists('idx_app_status_sprint_a');

            // Remover colunas
            $columns = [
                'status_sprint_a', 'discovery_opportunity_score', 'opportunity_score_breakdown',
                'purchase_intent_score', 'purchase_intent_label', 'purchase_intent_breakdown',
                'actual_performance_score', 'performance_score_breakdown',
                'approved_at', 'published_at', 'expired_at', 'expires_at', 'expiration_reason',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('product_opportunities', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
