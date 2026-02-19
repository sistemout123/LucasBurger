<?php

namespace App\Enums;

enum OrderStatus: string
{
    case OPEN = 'open';
    case PREPARING = 'preparing';
    case READY = 'ready';
    case PAID = 'paid';
    case CANCELED = 'canceled';

    public function getLabel(): string
    {
        return match ($this) {
            self::OPEN => 'Aberto',
            self::PREPARING => 'Preparando',
            self::READY => 'Pronto',
            self::PAID => 'Pago',
            self::CANCELED => 'Cancelado',
        };
    }
}
