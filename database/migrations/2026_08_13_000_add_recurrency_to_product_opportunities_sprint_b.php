<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adicionar suporte a Recurrency Factor para Sprint B.
     * Recurrency = conversões / 90 dias, normalizado 0-100.
     */
    public function up(): void
    {
        Schema::table('product_opportunities', function (Blueprint $table) {
            if (! Schema::hasColumn('product_opportunities', 'recurrency_rate')) {
                $table->decimal('recurrency_rate', 8, 2)
                    ->nullable()
                    ->after('actual_performance_score')
                    ->comment('Taxa de recorrência (Sprint B): conversões / 90 dias, normalizado 0-100');
            }

            if (! Schema::hasColumn('product_opportunities', 'confidence_level')) {
                $table->enum('confidence_level', ['INSUFFICIENT_DATA', 'LOW', 'MEDIUM', 'HIGH'])
                    ->nullable()
                    ->after('recurrency_rate')
                    ->comment('Nível de confiança do performance score (Sprint B): baseado em número de fatores disponíveis');
            }

            // Índice para queries que filtram por confidence level
            if (! Schema::hasIndex('product_opportunities', 'idx_confidence_level')) {
                $table->index(['application_id', 'confidence_level'], 'idx_confidence_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_opportunities', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_confidence_level');

            if (Schema::hasColumn('product_opportunities', 'recurrency_rate')) {
                $table->dropColumn('recurrency_rate');
            }

            if (Schema::hasColumn('product_opportunities', 'confidence_level')) {
                $table->dropColumn('confidence_level');
            }
        });
    }
};
