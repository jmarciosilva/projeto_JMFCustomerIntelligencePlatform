<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adicionar UNIQUE constraint em affiliate_conversions.
     * Garante idempotência: (application_id, provider, external_conversion_id).
     * DEVE rodar APÓS backfill de provider e validação de duplicidades.
     */
    public function up(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            if (!Schema::hasIndex('affiliate_conversions', 'uq_app_provider_external_id')) {
                $table->unique(['application_id', 'provider', 'external_conversion_id'], 'uq_app_provider_external_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('affiliate_conversions', function (Blueprint $table) {
            $table->dropUniqueIfExists('uq_app_provider_external_id');
        });
    }
};
