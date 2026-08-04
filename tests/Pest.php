<?php

use App\Domain\Shared\Enums\AdminRole;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
