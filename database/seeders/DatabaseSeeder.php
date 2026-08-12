<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            AdminUserSeeder::class,
            AudienceSegmentSeeder::class,
            AffiliateWorkspaceSeeder::class,
            Phase22AffiliateIntelligenceSeeder::class,
            Phase23TrendIntelligenceSeeder::class,
            Phase24TrendScoreSeeder::class,
            SprintATestDataSeeder::class,
        ]);
    }
}
