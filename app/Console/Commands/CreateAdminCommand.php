<?php

namespace App\Console\Commands;

use App\Domain\Shared\Enums\AdminRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CreateAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um administrador (Super Admin) da plataforma';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->ask('Nome');
        $email = $this->ask('E-mail');
        $password = $this->secret('Senha (mínimo 8 caracteres)');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);

        $role = Role::findOrCreate(AdminRole::SuperAdmin->value, 'web');
        $user->assignRole($role);

        $this->info("Administrador '{$user->email}' criado com sucesso com a role '".AdminRole::SuperAdmin->value."'.");

        return self::SUCCESS;
    }
}
