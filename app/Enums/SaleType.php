<?php

namespace App\Enums;

enum SaleType: string
{
    case ON_DEMAND = 'on_demand';
    case STOCK = 'stock';

    public function label(): string
    {
        return match ($this) {
            self::ON_DEMAND => 'Sob demanda',
            self::STOCK => 'Em estoque',
        };
    }
}
