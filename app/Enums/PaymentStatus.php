<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case OVERDUE = 'overdue';
    case REFUNDED = 'refunded';
    case CHARGEBACK = 'chargeback';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente', self::CONFIRMED => 'Confirmado', self::OVERDUE => 'Vencido', self::REFUNDED => 'Estornado', self::CHARGEBACK => 'Chargeback', self::FAILED => 'Falhou'
        };
    }
}
