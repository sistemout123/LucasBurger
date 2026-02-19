<?php

namespace App\Enums;

enum TableStatus: string
{
    case FREE = 'free';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';

    public function getLabel(): string
    {
        return match ($this) {
            self::FREE => 'Livre',
            self::OCCUPIED => 'Ocupada',
            self::RESERVED => 'Reservada',
        };
    }
}
