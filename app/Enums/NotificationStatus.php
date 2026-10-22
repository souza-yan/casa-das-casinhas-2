<?php

namespace App\Enums;

enum NotificationStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente', self::SENT => 'Enviada', self::DELIVERED => 'Entregue', self::FAILED => 'Falhou'
        };
    }
}
