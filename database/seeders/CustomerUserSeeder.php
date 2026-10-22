<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class CustomerUserSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('O CustomerUserSeeder não pode ser executado em produção.');
        }

        User::query()->updateOrCreate(
            ['email' => config('customer.email')],
            [
                'name' => config('customer.name'),
                'password' => config('customer.password'),
                'role' => Role::CUSTOMER,
            ],
        );

        $this->command?->info('Cliente disponível em '.config('customer.email').'.');
    }
}
