<?php

namespace Tests\Unit;

use App\Models\Product;
use PHPUnit\Framework\TestCase;

class ProductDefaultsTest extends TestCase
{
    public function test_production_period_is_always_fifteen_days(): void
    {
        $product = new Product;
        $product->production_days = 3;

        $this->assertSame(Product::PRODUCTION_DAYS, $product->production_days);
        $this->assertSame(Product::PRODUCTION_DAYS, $product->getAttributes()['production_days']);
    }
}
