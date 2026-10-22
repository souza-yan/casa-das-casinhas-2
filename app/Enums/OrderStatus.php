<?php

namespace App\Enums;

use DomainException;

enum OrderStatus: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case IN_PRODUCTION = 'in_production';
    case READY_TO_SHIP = 'ready_to_ship';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'Aguardando pagamento', self::PAID => 'Pago',
            self::IN_PRODUCTION => 'Em produção', self::READY_TO_SHIP => 'Pronto para envio',
            self::SHIPPED => 'Enviado', self::DELIVERED => 'Entregue', self::CANCELLED => 'Cancelado',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, match ($this) {
            self::PENDING_PAYMENT => [self::PAID, self::CANCELLED],
            self::PAID => [self::IN_PRODUCTION, self::CANCELLED],
            self::IN_PRODUCTION => [self::READY_TO_SHIP, self::CANCELLED],
            self::READY_TO_SHIP => [self::SHIPPED, self::CANCELLED],
            self::SHIPPED => [self::DELIVERED],
            self::DELIVERED, self::CANCELLED => [],
        }, true);
    }

    public function assertCanTransitionTo(self $next): void
    {
        throw_unless($this->canTransitionTo($next), DomainException::class, "Transição inválida de {$this->value} para {$next->value}.");
    }
}
