<?php

namespace Database\Seeders;

use App\Domain\Shared\Enums\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (blank($email) || blank($password)) {
            $this->command->warn(
                'ADMIN_EMAIL/ADMIN_PASSWORD não definidos no .env — nenhum administrador foi criado automaticamente. '
                .'Use "php artisan admin:create" para criar um manualmente.'
            );

            return;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $this->command->info("Administrador '{$email}' já existe — nenhuma alteração feita.");
        } else {
            $user = User::create([
                'name' => config('admin.name'),
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);

            $this->command->info("Administrador '{$email}' criado.");
        }

        $role = Role::findOrCreate(AdminRole::SuperAdmin->value, 'web');
        $user->assignRole($role);
    }
}
