<?php

namespace App\Services\NFe;

use App\Models\Ingredient;
use App\DTOs\NFeItemDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ItemReconcilerService
{
    /**
     * @param Collection<int, NFeItemDTO> $items
     * @return Collection<int, NFeItemDTO>
     */
    public function reconcile(Collection $items): Collection
    {
        // Cache all ingredients to avoid hitting the DB in a loop for each item
        $ingredients = Ingredient::select('id', 'name')->get();

        return $items->map(function (NFeItemDTO $item) use ($ingredients) {
            $normalizedName = $this->normalizeString($item->original_name);

            // Try exact match first
            $match = $ingredients->first(function ($ingredient) use ($normalizedName) {
                return $this->normalizeString($ingredient->name) === $normalizedName;
            });

            // If no exact match, try simple word inclusion
            if (!$match) {
                $match = $ingredients->first(function ($ingredient) use ($normalizedName) {
                    $ingredientNormalized = $this->normalizeString($ingredient->name);

                    // Simple heuristic: if the ingredient name is fully contained in the NFe item name
                    if (str_contains($normalizedName, $ingredientNormalized)) {
                        return true;
                    }
                    return false;
                });
            }

            if ($match) {
                $item->matched_ingredient_id = $match->id;
            }

            return $item;
        });
    }

    private function normalizeString(string $subject): string
    {
        // Lowercase, remove accents, and trim whitespace
        $ascii = Str::ascii($subject);
        return Str::lower(trim($ascii));
    }
}
