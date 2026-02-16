<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum StockMovementType: string implements HasLabel
{
    case IN = 'in';
    case OUT = 'out';
    case ADJUSTMENT = 'adjustment';
    case WASTE = 'waste';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::IN => 'Entrada',
            self::OUT => 'Saída',
            self::ADJUSTMENT => 'Ajuste',
            self::WASTE => 'Perda',
        };
    }
}
