<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Tenant;
use App\Models\Trend;
use App\Models\Watchlist;
use Illuminate\Database\Seeder;

class Phase23TrendIntelligenceSeeder extends Seeder
{
    /**
     * Cria dados de teste para Fase 23 - Trend Intelligence
     * (watchlists, trends coletados e histórico de sinais)
     */
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'jmf-system')->firstOrFail();
        $application = Application::where('tenant_id', $tenant->id)
            ->where('slug', 'magazine-voce-afiliados')
            ->firstOrFail();

        // Watchlist 1: Eletrônicos e Gadgets
        $watchlistTech = Watchlist::firstOrCreate(
            ['application_id' => $application->id, 'name' => 'Eletrônicos & Gadgets'],
            [
                'description' => 'Monitoramento de tendências em tecnologia e eletrônicos',
                'keywords' => ['smartphone', 'notebook', 'fone', 'smartwatch', 'drone'],
                'hashtags' => ['#tech', '#gadget', '#eletrônicos', '#inovação'],
                'categories' => ['Eletrônicos', 'Computadores', 'Áudio'],
                'collection_frequency' => Watchlist::FREQUENCY_DAILY,
                'status' => Watchlist::STATUS_ACTIVE,
            ]
        );

        // Watchlist 2: Moda e Beleza
        $watchlistFashion = Watchlist::firstOrCreate(
            ['application_id' => $application->id, 'name' => 'Moda & Beleza'],
            [
                'description' => 'Acompanhamento de tendências de moda, cosméticos e cuidados',
                'keywords' => ['moda', 'beleza', 'cosmético', 'skincare', 'maquiagem'],
                'hashtags' => ['#moda', '#beleza', '#estilo', '#skincare', '#makeup'],
                'categories' => ['Moda', 'Beleza', 'Cosméticos'],
                'collection_frequency' => Watchlist::FREQUENCY_WEEKLY,
                'status' => Watchlist::STATUS_ACTIVE,
            ]
        );

        // Watchlist 3: Casa e Lifestyle
        $watchlistHome = Watchlist::firstOrCreate(
            ['application_id' => $application->id, 'name' => 'Casa & Lifestyle'],
            [
                'description' => 'Tendências em decoração, móveis e lifestyle',
                'keywords' => ['decoração', 'móvel', 'lifestyle', 'casa', 'organização'],
                'hashtags' => ['#homedecor', '#lifestyle', '#design', '#organizado'],
                'categories' => ['Casa', 'Decoração', 'Móveis'],
                'collection_frequency' => Watchlist::FREQUENCY_WEEKLY,
                'status' => Watchlist::STATUS_ACTIVE,
            ]
        );

        // Trends da Watchlist Tech
        $techTrends = [
            ['term' => 'smartphone foldable', 'type' => Trend::TYPE_KEYWORD],
            ['term' => 'ia em eletrônicos', 'type' => Trend::TYPE_KEYWORD],
            ['term' => 'notebook gamer', 'type' => Trend::TYPE_KEYWORD],
            ['term' => '#techtrends2026', 'type' => Trend::TYPE_HASHTAG],
            ['term' => '#gadgetdesembrulho', 'type' => Trend::TYPE_HASHTAG],
        ];

        foreach ($techTrends as $trendData) {
            Trend::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'watchlist_id' => $watchlistTech->id,
                    'term' => $trendData['term'],
                ],
                [
                    'type' => $trendData['type'],
                    'status' => Trend::STATUS_ACTIVE,
                    'last_collected_at' => now()->subHours(rand(1, 24)),
                    'trend_score' => rand(45, 95),
                    'trend_score_breakdown' => [
                        'mentions' => rand(100, 5000),
                        'engagement' => rand(50, 2000),
                        'velocity' => rand(10, 200),
                    ],
                    'trend_score_computed_at' => now()->subHours(2),
                ]
            );
        }

        // Trends da Watchlist Fashion
        $fashionTrends = [
            ['term' => 'y2k fashion', 'type' => Trend::TYPE_KEYWORD],
            ['term' => 'moda sustentável', 'type' => Trend::TYPE_KEYWORD],
            ['term' => 'athleisure', 'type' => Trend::TYPE_KEYWORD],
            ['term' => '#moda2026', 'type' => Trend::TYPE_HASHTAG],
            ['term' => '#fashiontrend', 'type' => Trend::TYPE_HASHTAG],
        ];

        foreach ($fashionTrends as $trendData) {
            Trend::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'watchlist_id' => $watchlistFashion->id,
                    'term' => $trendData['term'],
                ],
                [
                    'type' => $trendData['type'],
                    'status' => Trend::STATUS_ACTIVE,
                    'last_collected_at' => now()->subHours(rand(1, 48)),
                    'trend_score' => rand(40, 90),
                    'trend_score_breakdown' => [
                        'mentions' => rand(200, 8000),
                        'engagement' => rand(100, 3000),
                        'velocity' => rand(20, 300),
                    ],
                    'trend_score_computed_at' => now()->subHours(4),
                ]
            );
        }

        // Trends da Watchlist Home
        $homeTrends = [
            ['term' => 'decoração minimalista', 'type' => Trend::TYPE_KEYWORD],
            ['term' => 'organização casa', 'type' => Trend::TYPE_KEYWORD],
            ['term' => 'plantas decorativas', 'type' => Trend::TYPE_KEYWORD],
            ['term' => '#homedecor2026', 'type' => Trend::TYPE_HASHTAG],
            ['term' => '#interiordecor', 'type' => Trend::TYPE_HASHTAG],
        ];

        foreach ($homeTrends as $trendData) {
            Trend::firstOrCreate(
                [
                    'application_id' => $application->id,
                    'watchlist_id' => $watchlistHome->id,
                    'term' => $trendData['term'],
                ],
                [
                    'type' => $trendData['type'],
                    'status' => Trend::STATUS_ACTIVE,
                    'last_collected_at' => now()->subDays(rand(1, 7)),
                    'trend_score' => rand(35, 85),
                    'trend_score_breakdown' => [
                        'mentions' => rand(150, 6000),
                        'engagement' => rand(80, 2500),
                        'velocity' => rand(15, 250),
                    ],
                    'trend_score_computed_at' => now()->subDays(1),
                ]
            );
        }

        $totalTrends = Trend::where('application_id', $application->id)->count();
        $this->command->info("Phase 23 - Trend Intelligence: {$totalTrends} trends criados em 3 watchlists.");
    }
}
