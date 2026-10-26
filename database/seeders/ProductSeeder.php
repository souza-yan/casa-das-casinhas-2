<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('O ProductSeeder de demonstração não pode ser executado em produção.');
        }

        $products = [
            [
                'slug' => 'casinha',
                'name' => 'Casinha',
                'description' => 'Casinha artesanal de madeira, montada e pintada individualmente.',
                'price' => 50.00,
                'image_path' => 'products/barracao-produto-1200x1500.png',
                'height_cm' => 30,
                'width_cm' => 40,
                'length_cm' => 5,
            ],
            [
                'slug' => 'vila-de-2-casas',
                'name' => 'Dupla de casinhas',
                'description' => 'Conjunto artesanal com duas casinhas de madeira, montadas e pintadas individualmente.',
                'price' => 100.00,
                'image_path' => 'products/vila-de-2-casas-1200x1500.png',
                'height_cm' => 30,
                'width_cm' => 40,
                'length_cm' => 5,
            ],
            [
                'slug' => 'vila-de-3-casas',
                'name' => 'Vila de 3 casas',
                'description' => 'Vila artesanal com três casinhas de madeira, montadas e pintadas individualmente.',
                'price' => 150.00,
                'image_path' => 'products/vila-de-3-casas-1200x1500.png',
                'height_cm' => 30,
                'width_cm' => 40,
                'length_cm' => 5,
            ],
            [
                'slug' => 'vila-de-4-casas',
                'name' => 'Vila de 4 casas',
                'description' => 'Vila artesanal com quatro casinhas de madeira, montadas e pintadas individualmente.',
                'price' => 200.00,
                'image_path' => 'products/vila-de-4-casas-1200x1500.png',
                'height_cm' => 30,
                'width_cm' => 40,
                'length_cm' => 5,
            ],
            [
                'slug' => 'favela-de-5-casas',
                'name' => 'Favela de 5 casas',
                'description' => 'Conjunto artesanal com cinco casas de madeira, montadas e pintadas individualmente.',
                'price' => 250.00,
                'image_path' => 'products/vila-de-5-casas-1200x1500.png',
                'height_cm' => 30,
                'width_cm' => 40,
                'length_cm' => 5,
            ],
        ];

        foreach ($products as $data) {
            if (str_starts_with($data['image_path'], 'products/')) {
                $sourcePath = database_path('seeders/assets/'.$data['image_path']);

                if (is_file($sourcePath)) {
                    Storage::disk('public')->put($data['image_path'], file_get_contents($sourcePath));
                } elseif (! Storage::disk('public')->exists($data['image_path'])) {
                    throw new RuntimeException("Imagem de origem não encontrada: {$sourcePath}");
                }
            }

            $product = Product::withTrashed()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    ...$data,
                    'weight_kg' => Product::DEFAULT_WEIGHT_KG,
                    'production_days' => Product::PRODUCTION_DAYS,
                    'is_customizable' => true,
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            );

            if ($product->trashed()) {
                $product->restore();
            }
        }

        $this->command?->info(count($products).' produtos disponíveis para compra.');
    }
}
