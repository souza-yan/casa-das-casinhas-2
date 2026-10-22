<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('O AdminUserSeeder não pode ser executado em produção.');
        }

        $email = (string) config('admin.email');
        $password = (string) config('admin.password');

        if ($email === '' || $password === '') {
            throw new RuntimeException('Configure ADMIN_EMAIL e ADMIN_PASSWORD antes de criar o administrador.');
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => config('admin.name'),
                'password' => $password,
                'role' => Role::ADMIN,
            ],
        );

        $this->command?->info("Administrador disponível em {$email}.");
    }
}
