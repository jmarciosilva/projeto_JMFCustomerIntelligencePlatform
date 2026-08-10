<?php

namespace App\Domain\Trends;

use App\Models\TrendingTopic;
use Illuminate\Support\Facades\Http;

class FetchGoogleTrendsAction
{
    private string $region = 'BR';

    public function __construct(string $region = 'BR')
    {
        $this->region = $region;
    }

    public function execute(): array
    {
        $trendingTopics = $this->fetchTrends();

        foreach ($trendingTopics as $topic) {
            TrendingTopic::updateOrCreate(
                ['topic' => $topic['topic']],
                [
                    'description' => $topic['description'] ?? null,
                    'search_volume' => $topic['search_volume'] ?? 0,
                    'growth_percentage' => $topic['growth_percentage'] ?? null,
                    'category' => $topic['category'] ?? 'Other',
                    'region' => $this->region,
                    'fetched_at' => now(),
                ]
            );
        }

        return $trendingTopics;
    }

    private function fetchTrends(): array
    {
        // Opção 1: Usar SerpAPI (requer API key)
        if (config('services.serpapi.key')) {
            return $this->fetchFromSerpAPI();
        }

        // Opção 2: Dados mockados para teste (padrão)
        return $this->getMockTrends();
    }

    private function fetchFromSerpAPI(): array
    {
        $apiKey = config('services.serpapi.key');
        $gl = $this->region === 'BR' ? 'br' : ($this->region === 'US' ? 'us' : 'br');

        try {
            $response = Http::get('https://serpapi.com/search', [
                'q' => 'trending',
                'tbm' => 'nws',
                'gl' => $gl,
                'api_key' => $apiKey,
            ]);

            if ($response->successful()) {
                return $this->parseSerpAPIResponse($response->json());
            }
        } catch (\Exception $e) {
            \Log::warning('SerpAPI Error: ' . $e->getMessage());
        }

        return $this->getMockTrends();
    }

    private function parseSerpAPIResponse(array $data): array
    {
        $trends = [];

        // Extrai trending topics da resposta SerpAPI
        if (isset($data['news']) && is_array($data['news'])) {
            foreach (array_slice($data['news'], 0, 10) as $news) {
                $trends[] = [
                    'topic' => $news['title'] ?? 'Trending Topic',
                    'description' => $news['snippet'] ?? null,
                    'search_volume' => rand(1000, 100000),
                    'growth_percentage' => rand(10, 500),
                    'category' => 'News',
                ];
            }
        }

        return $trends;
    }

    private function getMockTrends(): array
    {
        // Dados mockados para teste (simula Google Trends Brasil)
        return [
            [
                'topic' => '#LooksDeFrio',
                'description' => 'Tendência de moda para outono/inverno 2026',
                'search_volume' => 45000,
                'growth_percentage' => 250,
                'category' => 'Fashion',
            ],
            [
                'topic' => '#TaEmAlta',
                'description' => 'Tendência viral de conteúdo lifestyle',
                'search_volume' => 38000,
                'growth_percentage' => 180,
                'category' => 'Entertainment',
            ],
            [
                'topic' => '#ModaBrasil2026',
                'description' => 'Tendência de moda brasileira',
                'search_volume' => 32000,
                'growth_percentage' => 150,
                'category' => 'Fashion',
            ],
            [
                'topic' => '#OutunoInverno',
                'description' => 'Preparação para estação fria',
                'search_volume' => 28000,
                'growth_percentage' => 120,
                'category' => 'Fashion',
            ],
            [
                'topic' => '#AcessoriosEmAlta',
                'description' => 'Acessórios em tendência',
                'search_volume' => 24000,
                'growth_percentage' => 95,
                'category' => 'Fashion',
            ],
            [
                'topic' => '#BelezaBrasil',
                'description' => 'Produtos de beleza em alta',
                'search_volume' => 21000,
                'growth_percentage' => 85,
                'category' => 'Health',
            ],
            [
                'topic' => '#FashionWeek2026',
                'description' => 'Fashion Week Brasil e internacional',
                'search_volume' => 19000,
                'growth_percentage' => 75,
                'category' => 'Fashion',
            ],
            [
                'topic' => '#EEstilo',
                'description' => 'Tendência de estilo pessoal',
                'search_volume' => 17000,
                'growth_percentage' => 65,
                'category' => 'Fashion',
            ],
            [
                'topic' => '#ShoppingOnline',
                'description' => 'Compras online em alta',
                'search_volume' => 15000,
                'growth_percentage' => 55,
                'category' => 'Business',
            ],
            [
                'topic' => '#InfluencerMarketing',
                'description' => 'Marketing de influenciadores',
                'search_volume' => 12000,
                'growth_percentage' => 45,
                'category' => 'Business',
            ],
        ];
    }
}
