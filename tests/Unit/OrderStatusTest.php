<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

class OrderStatusTest extends TestCase
{
    public function test_valid_production_flow_is_allowed(): void
    {
        $this->assertTrue(OrderStatus::PENDING_PAYMENT->canTransitionTo(OrderStatus::PAID));
        $this->assertTrue(OrderStatus::PAID->canTransitionTo(OrderStatus::IN_PRODUCTION));
        $this->assertTrue(OrderStatus::IN_PRODUCTION->canTransitionTo(OrderStatus::READY_TO_SHIP));
        $this->assertTrue(OrderStatus::READY_TO_SHIP->canTransitionTo(OrderStatus::SHIPPED));
        $this->assertTrue(OrderStatus::SHIPPED->canTransitionTo(OrderStatus::DELIVERED));
    }

    public function test_unpaid_order_cannot_be_ready_to_ship(): void
    {
        $this->expectException(DomainException::class);
        OrderStatus::PENDING_PAYMENT->assertCanTransitionTo(OrderStatus::READY_TO_SHIP);
    }
}
