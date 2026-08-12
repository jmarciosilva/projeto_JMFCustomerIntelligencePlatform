<?php

namespace Database\Seeders;

use App\Domain\Affiliate\Enums\PurchaseIntentLabel;
use App\Domain\Affiliate\Enums\StatusSprintA;
use App\Models\AffiliateProduct;
use App\Models\AffiliateProgram;
use App\Models\Application;
use App\Models\ProductOpportunity;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\User;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;

class SprintATestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Criar tenant de teste
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'test-affiliate'],
            [
                'name' => 'Teste Afiliados',
                'is_active' => true,
            ]
        );

        // Criar aplicação de teste
        $application = Application::firstOrCreate(
            ['slug' => 'test-app'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Aplicação Teste',
                'is_active' => true,
                'conversion_event_name' => 'sale',
            ]
        );

        // Criar usuário de teste
        $user = User::firstOrCreate(
            ['email' => 'teste@afiliado.com'],
            [
                'name' => 'Testador Afiliado',
                'password' => bcrypt('senha123'),
                'is_active' => true,
            ]
        );

        // Criar programa afiliado
        $program = AffiliateProgram::firstOrCreate(
            [
                'application_id' => $application->id,
                'slug' => 'magalu-afiliados',
            ],
            [
                'name' => 'Magalu Afiliados',
                'provider' => 'magalu',
                'description' => 'Programa de afiliados Magazine Luiza',
            ]
        );

        // Criar watchlist para trends
        $watchlist = Watchlist::firstOrCreate(
            [
                'application_id' => $application->id,
                'name' => 'Produtos Populares',
            ],
            [
                'description' => 'Watchlist de produtos populares para teste de Sprint A',
            ]
        );

        // Criar trends
        $trends = [
            'Notebook gamer 2026',
            'Headset wireless',
            'Mouse mecânico RGB',
            'Monitor 4K',
            'Webcam profissional',
            'Teclado mecânico',
            'Mousepad grande',
            'Carregador rápido',
        ];

        $createdTrends = [];
        foreach ($trends as $term) {
            $trend = Trend::firstOrCreate(
                [
                    'watchlist_id' => $watchlist->id,
                    'term' => $term,
                ],
                [
                    'application_id' => $application->id,
                    'type' => 'keyword',
                    'status' => 'active',
                ]
            );
            $createdTrends[] = $trend;
        }

        // Criar produtos afiliados
        $products = [
            ['name' => 'Notebook Gamer ASUS', 'category' => 'Eletrônicos'],
            ['name' => 'Headset HyperX Cloud II', 'category' => 'Periféricos'],
            ['name' => 'Mouse Razer Basilisk', 'category' => 'Periféricos'],
            ['name' => 'Monitor LG 4K 27"', 'category' => 'Monitores'],
            ['name' => 'Webcam Logitech C920', 'category' => 'Webcams'],
            ['name' => 'Teclado Ducky One 2', 'category' => 'Teclados'],
            ['name' => 'Mousepad SteelSeries', 'category' => 'Acessórios'],
            ['name' => 'Carregador Baseus 65W', 'category' => 'Carregadores'],
            ['name' => 'Processador Intel i9', 'category' => 'Componentes'],
            ['name' => 'Placa RTX 4080', 'category' => 'Componentes'],
        ];

        $createdProducts = [];
        foreach ($products as $idx => $product) {
            $affiliateProduct = AffiliateProduct::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'affiliate_program_id' => $program->id,
                    'name' => $product['name'],
                ],
                [
                    'description' => "Descrição de {$product['name']}",
                    'category' => $product['category'],
                    'affiliate_url' => "https://example.com/product/{$idx}",
                    'external_product_id' => "ext-{$idx}",
                ]
            );
            $createdProducts[] = $affiliateProduct;
        }

        // Criar oportunidades de produtos com variados status
        $statuses = [
            StatusSprintA::DISCOVERED,
            StatusSprintA::ANALYZING,
            StatusSprintA::APPROVED,
            StatusSprintA::REJECTED,
            StatusSprintA::PUBLISHED,
        ];

        $labels = [
            PurchaseIntentLabel::LOW,
            PurchaseIntentLabel::MEDIUM,
            PurchaseIntentLabel::HIGH,
        ];

        $opportunityCount = 0;
        foreach ($createdTrends as $index => $trend) {
            if ($index >= count($createdProducts)) {
                break;
            }

            $product = $createdProducts[$index];
            $status = $statuses[$opportunityCount % count($statuses)];
            $label = $labels[array_rand($labels)];

            ProductOpportunity::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'trend_id' => $trend->id,
                    'affiliate_product_id' => $product->id,
                ],
                [
                    'status_sprint_a' => $status,
                    'discovery_opportunity_score' => rand(30, 95),
                    'opportunity_score_breakdown' => [
                        'trend_score' => rand(30, 100),
                        'match_score' => rand(30, 100),
                        'intent_score' => rand(0, 100),
                        'commission' => rand(5, 50),
                        'popularity' => rand(0, 100),
                    ],
                    'purchase_intent_score' => rand(40, 90),
                    'purchase_intent_label' => $label,
                    'purchase_intent_breakdown' => [
                        'base_intent' => ['INFORMATIONAL', 'INVESTIGATION', 'TRANSACTIONAL'][rand(0, 2)],
                        'adjustments' => [],
                    ],
                    'opportunity_score' => rand(30, 95),
                    'opportunity_breakdown' => [
                        'trend_score' => rand(30, 100),
                        'match_score' => rand(30, 100),
                        'intent' => ['high', 'medium', 'low'][rand(0, 2)],
                    ],
                    'commercial_intent' => ['high', 'medium', 'low'][rand(0, 2)],
                    'expires_at' => now()->addDays(rand(7, 30)),
                    'approved_at' => $status === StatusSprintA::APPROVED ? now() : null,
                    'published_at' => $status === StatusSprintA::PUBLISHED ? now() : null,
                ]
            );

            $opportunityCount++;
        }

        $this->command->info('Sprint A test data criado com sucesso!');
        $this->command->info("  - Tenant: {$tenant->name}");
        $this->command->info("  - Application: {$application->name}");
        $this->command->info("  - Watchlist: {$watchlist->name}");
        $this->command->info('  - Usuário teste: teste@afiliado.com (senha: senha123)');
        $this->command->info('  - Trends: '.count($createdTrends));
        $this->command->info('  - Produtos: '.count($createdProducts));
        $this->command->info("  - Oportunidades: {$opportunityCount}");
        $this->command->info("\nAcesse em: http://jmf-customer-intelligence.test/admin/affiliate/product-opportunities");
    }
}
