<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adicionar contact_id para rastrear recurrency de contatos.
     * Sprint B2: Necessário para calcular recurrency_rate do contato.
     */
    public function up(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            if (! Schema::hasColumn('affiliate_conversions', 'contact_id')) {
                $table->unsignedBigInteger('contact_id')
                    ->nullable()
                    ->after('application_id')
                    ->comment('ID do contato/lead que realizou a conversão (Sprint B2: Recurrency)');
            }

            // Índice para queries eficientes de recurrency (últimos 90 dias)
            if (! Schema::hasIndex('affiliate_conversions', 'idx_app_contact_created')) {
                $table->index(['application_id', 'contact_id', 'created_at'], 'idx_app_contact_created');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_app_contact_created');

            if (Schema::hasColumn('affiliate_conversions', 'contact_id')) {
                $table->dropColumn('contact_id');
            }
        });
    }
};
