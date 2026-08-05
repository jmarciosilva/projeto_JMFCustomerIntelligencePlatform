<?php

use App\Domain\Shared\Enums\AdminRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');

function seedAdminRoles(): void
{
    (new RolePermissionSeeder)->run();
}

/**
 * @param  array<string, mixed>  $attributes
 */
function superAdmin(array $attributes = []): User
{
    seedAdminRoles();

    $user = User::factory()->create($attributes);
    $user->assignRole(AdminRole::SuperAdmin->value);

    return $user;
}

/**
 * @param  array<string, mixed>  $attributes
 */
function administrador(array $attributes = []): User
{
    seedAdminRoles();

    $user = User::factory()->create($attributes);
    $user->assignRole(AdminRole::Administrador->value);

    return $user;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validEventPayload(array $overrides = []): array
{
    return array_merge([
        'event_id' => (string) Str::ulid(),
        'event_name' => 'article.viewed',
        'visitor_id' => 'visitor_001',
        'session_id' => 'session_001',
        'occurred_at' => now()->toIso8601String(),
        'properties' => ['article_id' => 15, 'category' => 'Laravel'],
        'context' => ['page_url' => '/blog/laravel-arquitetura'],
    ], $overrides);
}
