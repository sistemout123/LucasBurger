<?php

namespace App\DTOs;

use Illuminate\Support\Collection;

class NFeImportDTO
{
    public function __construct(
        public string $access_key,
        public ?string $supplier_name,
        public ?string $supplier_cnpj,
        public \DateTimeImmutable|null $issue_date,
        /** @var Collection<int, NFeItemDTO> */
        public Collection $items
    ) {
    }
}
