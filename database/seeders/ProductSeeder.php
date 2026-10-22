<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('O ProductSeeder de demonstração não pode ser executado em produção.');
        }

        $product = Product::withTrashed()->updateOrCreate(
            ['slug' => 'casas-da-vila'],
            [
                'name' => 'Casas da Vila',
                'description' => 'Painel artesanal em madeira MDF, pintado à mão, com casinhas coloridas, igreja central e cenas que celebram a vida, a cultura e a memória das vilas brasileiras.',
                'price' => 289.90,
                'image_path' => 'images/products/casas-da-vila.png',
                'weight_kg' => 3.500,
                'height_cm' => 40,
                'width_cm' => 80,
                'length_cm' => 5,
                'production_days' => 12,
                'is_active' => true,
                'deleted_at' => null,
            ],
        );

        $product->restore();
        $this->command?->info('Produto Casas da Vila disponível para compra.');
    }
}
