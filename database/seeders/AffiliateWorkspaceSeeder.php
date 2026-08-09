<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class AffiliateWorkspaceSeeder extends Seeder
{
    /**
     * Cria o Tenant/Application internos usados pelo laboratório de Trend
     * Intelligence & Affiliate Intelligence (Fases 22+), sem depender de
     * nenhum tenant/application de cliente já existente.
     */
    public function run(): void
    {
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'jmf-system'],
            ['name' => 'JMF System', 'is_active' => true]
        );

        $application = Application::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'magazine-voce-afiliados'],
            ['name' => 'Magazine Você — Afiliados', 'is_active' => true]
        );

        $this->command->info("Workspace de afiliados pronto: tenant '{$tenant->slug}', application '{$application->slug}' (id {$application->id}).");
    }
}
