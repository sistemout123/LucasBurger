<?php

namespace App\DTOs;

class NFeItemDTO
{
    public function __construct(
        public string $original_name,
        public float $quantity,
        public string $uom_symbol,
        public float $unit_price,
        public float $total_price,
        public ?int $matched_ingredient_id = null
    ) {
    }
}
