<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Administrador inicial (seeder)
    |--------------------------------------------------------------------------
    |
    | Usadas pelo AdminUserSeeder (php artisan db:seed) para criar o primeiro
    | Super Admin automaticamente em uma nova instalação/hospedagem. Se
    | ADMIN_EMAIL ou ADMIN_PASSWORD não estiverem definidas, o seeder pula
    | essa etapa e nenhuma credencial é criada — use `php artisan admin:create`
    | para criar um administrador manualmente nesse caso.
    |
    */

    'name' => env('ADMIN_NAME', 'Administrador'),
    'email' => env('ADMIN_EMAIL'),
    'password' => env('ADMIN_PASSWORD'),

];
