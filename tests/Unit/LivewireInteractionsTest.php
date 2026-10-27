<?php

namespace Tests\Unit;

use App\Livewire\Admin\Orders\Index as AdminOrders;
use App\Livewire\Admin\Orders\Show as AdminOrder;
use App\Livewire\Admin\Products\Index as AdminProducts;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\ProductDetail;
use App\Livewire\Profile;
use App\Livewire\Store;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TallStackUi\Traits\Interactions;

class LivewireInteractionsTest extends TestCase
{
    #[DataProvider('componentsWithActions')]
    public function test_actionable_components_use_tallstackui_interactions(string $component): void
    {
        $this->assertContains(Interactions::class, class_uses_recursive($component));
    }

    public static function componentsWithActions(): array
    {
        return [
            [Store::class],
            [ProductDetail::class],
            [Cart::class],
            [Checkout::class],
            [Profile::class],
            [AdminProducts::class],
            [AdminOrders::class],
            [AdminOrder::class],
        ];
    }
}
