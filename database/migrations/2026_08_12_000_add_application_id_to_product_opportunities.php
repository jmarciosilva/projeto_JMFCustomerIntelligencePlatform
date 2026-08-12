<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adicionar application_id a product_opportunities.
     * Necessário para Sprint A: tracking e isolamento por tenant.
     * Deve rodar ANTES de adicionar índices que dependem de application_id.
     */
    public function up(): void
    {
        Schema::table('product_opportunities', function (Blueprint $table) {
            if (! Schema::hasColumn('product_opportunities', 'application_id')) {
                // Adicionar como nullable primeiro, backfill depois via seeds/artisan
                $table->unsignedBigInteger('application_id')
                    ->nullable()
                    ->after('id')
                    ->comment('ID da aplicação (tenant)');

                // Índice para queries comuns
                $table->index('application_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_opportunities', function (Blueprint $table) {
            if (Schema::hasColumn('product_opportunities', 'application_id')) {
                $table->dropIndex(['application_id']);
                $table->dropColumn('application_id');
            }
        });
    }
};
