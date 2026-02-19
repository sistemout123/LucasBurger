<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PIX = 'pix';
    case CREDIT = 'credit';
    case DEBIT = 'debit';
    case CASH = 'cash';

    public function getLabel(): string
    {
        return match ($this) {
            self::PIX => 'PIX',
            self::CREDIT => 'Cartão de Crédito',
            self::DEBIT => 'Cartão de Débito',
            self::CASH => 'Dinheiro',
        };
    }
}
