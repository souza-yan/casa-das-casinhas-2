<?php

namespace App\Enums;

enum ShipmentStatus: string
{
    case QUOTED = 'quoted';
    case READY = 'ready';
    case LABEL_PURCHASED = 'label_purchased';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::QUOTED => 'Cotado', self::READY => 'Pronto', self::LABEL_PURCHASED => 'Etiqueta comprada', self::SHIPPED => 'Enviado', self::DELIVERED => 'Entregue', self::CANCELLED => 'Cancelado'
        };
    }
}
