<?php

namespace Database\Seeders;

use App\Models\AudienceSegment;
use Illuminate\Database\Seeder;

class AudienceSegmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $segments = [
            [
                'name' => 'Jovens Adultos',
                'description' => 'Públo de 18 a 35 anos, interessado em tendências de moda, lifestyle e tecnologia',
                'age_range_approx' => '18-35',
                'interests' => ['moda', 'lifestyle', 'tecnologia', 'viagens'],
                'purchase_intent_preference' => 'MEDIUM',
                'active' => true,
            ],
            [
                'name' => 'Profissionais',
                'description' => 'Profissionais de 25 a 55 anos com poder de compra e interesse em produtos de qualidade',
                'age_range_approx' => '25-55',
                'interests' => ['tecnologia', 'desenvolvimento pessoal', 'produtividade'],
                'purchase_intent_preference' => 'HIGH',
                'active' => true,
            ],
            [
                'name' => 'Pais',
                'description' => 'Responsáveis com filhos, interessados em produtos de segurança, educação e bem-estar infantil',
                'age_range_approx' => '30-50',
                'interests' => ['educação', 'segurança', 'bem-estar infantil', 'saúde'],
                'purchase_intent_preference' => 'HIGH',
                'active' => true,
            ],
            [
                'name' => 'Saúde & Bem-estar',
                'description' => 'Pessoas preocupadas com saúde, fitness, nutrição e bem-estar holístico',
                'age_range_approx' => '20-60',
                'interests' => ['fitness', 'nutrição', 'saúde', 'bem-estar'],
                'purchase_intent_preference' => 'HIGH',
                'active' => true,
            ],
            [
                'name' => 'Tecnologia Entusiastas',
                'description' => 'Adotadores precoces de tecnologia, interessados em inovação e produtos tech',
                'age_range_approx' => '18-45',
                'interests' => ['tecnologia', 'inovação', 'gadgets', 'programação'],
                'purchase_intent_preference' => 'MEDIUM',
                'active' => true,
            ],
            [
                'name' => 'Curiosos & Browsing',
                'description' => 'Público ocasional, explorando produtos por curiosidade sem intenção de compra imediata',
                'age_range_approx' => '18-70',
                'interests' => ['exploração', 'aprendizado'],
                'purchase_intent_preference' => 'LOW',
                'active' => true,
            ],
        ];

        foreach ($segments as $segment) {
            AudienceSegment::firstOrCreate(
                ['name' => $segment['name']],
                $segment
            );
        }
    }
}
