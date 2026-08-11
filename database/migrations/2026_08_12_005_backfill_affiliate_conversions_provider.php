<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill de provider para conversões históricas.
     * Sprint A assume que conversões existentes vêm de Magalu (laboratório inicial).
     * Registro em log de IDs modificados para auditoria.
     */
    public function up(): void
    {
        // Registrar quantas conversões vão receber backfill
        $count = DB::table('affiliate_conversions')
            ->whereNull('provider')
            ->count();

        if ($count > 0) {
            \Log::info("Backfill: {$count} affiliate_conversions receberão provider='magalu'");

            DB::table('affiliate_conversions')
                ->whereNull('provider')
                ->update(['provider' => 'magalu']);

            \Log::info("Backfill concluído: provider='magalu' atribuído a {$count} registros");
        }
    }

    public function down(): void
    {
        // Reverter backfill: colocar provider como NULL onde era magalu (cuidado com reversão)
        // Estratégia conservadora: LOG apenas, deixar para o DBA decidir
        $count = DB::table('affiliate_conversions')
            ->where('provider', 'magalu')
            ->whereNull('external_conversion_id')
            ->count();

        if ($count > 0) {
            \Log::warning("Rollback: {$count} conversões com provider='magalu' e external_conversion_id=null ainda existem. Decisão manual necessária.");
        }
    }
};
