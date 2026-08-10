<?php

namespace Database\Seeders;

use App\Models\AffiliateProgram;
use App\Models\AffiliateProduct;
use App\Models\Application;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class Phase22AffiliateIntelligenceSeeder extends Seeder
{
    /**
     * Cria dados de teste para Fase 22 - Affiliate Intelligence
     * (programas afiliados, produtos e histórico de importação CSV)
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'jmf-system')->firstOrFail();
        $application = Application::where('tenant_id', $tenant->id)
            ->where('slug', 'magazine-voce-afiliados')
            ->firstOrFail();

        // Programa 1: Magalu (importado via provider)
        $magalu = AffiliateProgram::firstOrCreate(
            ['application_id' => $application->id, 'slug' => 'magalu'],
            [
                'name' => 'Magalu Afiliados',
                'website' => 'https://afiliados.magazineluiza.com.br',
                'description' => 'Programa de afiliados do Magazine Luiza com comissões competitivas',
                'provider' => AffiliateProgram::PROVIDER_MAGALU,
                'status' => AffiliateProgram::STATUS_ACTIVE,
                'configuration' => [
                    'api_key' => 'demo_magalu_key_123',
                    'last_sync' => now()->subDays(2)->toIso8601String(),
                ],
            ]
        );

        // Programa 2: Manual (criado manualmente)
        $amazon = AffiliateProgram::firstOrCreate(
            ['application_id' => $application->id, 'slug' => 'amazon-br'],
            [
                'name' => 'Amazon Associados Brasil',
                'website' => 'https://associados.amazon.com.br',
                'description' => 'Programa de afiliados Amazon com cobertura completa',
                'provider' => AffiliateProgram::PROVIDER_MANUAL,
                'status' => AffiliateProgram::STATUS_ACTIVE,
                'configuration' => [],
            ]
        );

        // Programa 3: Inativos para teste
        AffiliateProgram::firstOrCreate(
            ['application_id' => $application->id, 'slug' => 'hotmart'],
            [
                'name' => 'Hotmart',
                'website' => 'https://hotmart.com',
                'description' => 'Plataforma de afiliados (desativada)',
                'provider' => AffiliateProgram::PROVIDER_MANUAL,
                'status' => AffiliateProgram::STATUS_INACTIVE,
                'configuration' => [],
            ]
        );

        // Produtos do Magalu
        $magsluProducts = [
            [
                'external_product_id' => 'mag-001',
                'name' => 'Smartphone Samsung Galaxy A53',
                'category' => 'Eletrônicos',
                'brand' => 'Samsung',
                'price' => 2199.90,
                'original_price' => 2499.90,
                'commission_percentage' => 3.50,
                'affiliate_url' => 'https://magalu.com.br/p/smartphone-samsung-galaxy-a53?ref=afiliado123',
            ],
            [
                'external_product_id' => 'mag-002',
                'name' => 'Fone Bluetooth JBL Tune 510',
                'category' => 'Áudio',
                'brand' => 'JBL',
                'price' => 349.90,
                'original_price' => 399.90,
                'commission_percentage' => 5.00,
                'affiliate_url' => 'https://magalu.com.br/p/fone-jbl-tune-510?ref=afiliado123',
            ],
            [
                'external_product_id' => 'mag-003',
                'name' => 'Notebook ASUS VivoBook 15',
                'category' => 'Computadores',
                'brand' => 'ASUS',
                'price' => 3499.90,
                'original_price' => 4299.90,
                'commission_percentage' => 2.50,
                'affiliate_url' => 'https://magalu.com.br/p/notebook-asus-vivobook?ref=afiliado123',
            ],
        ];

        foreach ($magsluProducts as $productData) {
            $productData['application_id'] = $application->id;
            $productData['affiliate_program_id'] = $magalu->id;
            $productData['description'] = "Descrição automática do produto {$productData['name']}";
            $productData['availability'] = AffiliateProduct::AVAILABILITY_IN_STOCK;
            $productData['last_checked_at'] = now();
            $productData['metadata'] = ['source' => 'magalu_api', 'category_id' => '123'];

            AffiliateProduct::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'external_product_id' => $productData['external_product_id'],
                ],
                $productData
            );
        }

        // Produtos do Amazon
        $amazonProducts = [
            [
                'external_product_id' => 'amz-001',
                'name' => 'Alexa Echo Dot 5ª Geração',
                'category' => 'Smart Home',
                'brand' => 'Amazon',
                'price' => 249.90,
                'original_price' => 349.90,
                'commission_percentage' => 4.00,
                'affiliate_url' => 'https://amazon.com.br/dp/B09XXXXX?tag=afiliado-123',
            ],
            [
                'external_product_id' => 'amz-002',
                'name' => 'Fire TV Stick 4K',
                'category' => 'Streaming',
                'brand' => 'Amazon',
                'price' => 389.90,
                'original_price' => 449.90,
                'commission_percentage' => 3.50,
                'affiliate_url' => 'https://amazon.com.br/dp/B08XXXXX?tag=afiliado-123',
            ],
        ];

        foreach ($amazonProducts as $productData) {
            $productData['application_id'] = $application->id;
            $productData['affiliate_program_id'] = $amazon->id;
            $productData['description'] = "Descrição automática do produto {$productData['name']}";
            $productData['availability'] = AffiliateProduct::AVAILABILITY_IN_STOCK;
            $productData['last_checked_at'] = now();
            $productData['metadata'] = ['source' => 'amazon_api'];

            AffiliateProduct::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'external_product_id' => $productData['external_product_id'],
                ],
                $productData
            );
        }

        $this->command->info('Phase 22 - Affiliate Intelligence: '.AffiliateProgram::where('application_id', $application->id)->count().' programas e '.AffiliateProduct::where('application_id', $application->id)->count().' produtos criados.');
    }
}
