<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Validar duplicidades em affiliate_conversions antes de criar UNIQUE constraint.
     * Se encontradas, remover duplicatas mais antigas (manter mais recente).
     * Registrar em log TODOS os IDs envolvidos.
     */
    public function up(): void
    {
        // Encontrar duplicatas potenciais
        $duplicates = DB::select('
            SELECT application_id, provider, external_conversion_id, COUNT(*) as count
            FROM affiliate_conversions
            WHERE application_id IS NOT NULL
              AND provider IS NOT NULL
              AND external_conversion_id IS NOT NULL
            GROUP BY application_id, provider, external_conversion_id
            HAVING COUNT(*) > 1
        ');

        if (! empty($duplicates)) {
            Log::warning('Duplicidades encontradas em affiliate_conversions', [
                'total_duplicates_detected' => count($duplicates),
                'duplicates' => array_map(fn ($d) => [
                    'application_id' => $d->application_id,
                    'provider' => $d->provider,
                    'external_conversion_id' => $d->external_conversion_id,
                    'count' => $d->count,
                ], $duplicates),
            ]);

            // Para cada chave duplicada, remover as cópias mais antigas
            foreach ($duplicates as $dup) {
                // Encontrar IDs dos registros duplicados (menos o mais recente)
                $to_delete = DB::table('affiliate_conversions')
                    ->where('application_id', $dup->application_id)
                    ->where('provider', $dup->provider)
                    ->where('external_conversion_id', $dup->external_conversion_id)
                    ->orderBy('created_at', 'desc')
                    ->skip(1) // Pular o mais recente
                    ->pluck('id')
                    ->toArray();

                if (! empty($to_delete)) {
                    Log::info('Removendo duplicatas', [
                        'application_id' => $dup->application_id,
                        'provider' => $dup->provider,
                        'external_conversion_id' => $dup->external_conversion_id,
                        'ids_to_delete' => $to_delete,
                        'ids_count' => count($to_delete),
                    ]);

                    DB::table('affiliate_conversions')
                        ->whereIn('id', $to_delete)
                        ->delete();
                }
            }

            Log::info('Validação de duplicidades concluída');
        } else {
            Log::info('Nenhuma duplicidade encontrada em affiliate_conversions');
        }
    }

    public function down(): void
    {
        // Reversão: não há como recuperar dados já deletados
        // Log apenas
        Log::warning('Rollback da validação de duplicidades: registros já foram deletados na migração forward. Recuperação manual pode ser necessária.');
    }
};
