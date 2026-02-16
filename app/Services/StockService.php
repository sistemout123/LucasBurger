<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Enums\StockMovementType;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Record a stock movement and update the ingredient's current stock.
     */
    public function recordMovement(Ingredient $ingredient, float $quantity, StockMovementType $type, ?string $reason = null, ?int $userId = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $quantity, $type, $reason, $userId) {

            $movement = new StockMovement([
                'ingredient_id' => $ingredient->id,
                'user_id' => $userId ?? auth()->id(),
                'quantity' => $quantity,
                'type' => $type,
                'reason' => $reason,
            ]);

            $movement->save();

            $this->updateIngredientStock($ingredient, $quantity, $type);

            return $movement;
        });
    }

    private function updateIngredientStock(Ingredient $ingredient, float $quantity, StockMovementType $type): void
    {
        switch ($type) {
            case StockMovementType::IN:
                $ingredient->increment('current_stock', $quantity);
                break;
            case StockMovementType::OUT:
            case StockMovementType::WASTE:
                $ingredient->decrement('current_stock', $quantity);
                break;
            case StockMovementType::ADJUSTMENT:
                // For adjustment, we assume the quantity provided IS the new stock level, 
                // OR we could handle it as a delta. 
                // Ideally, adjustments often mean "set to X". 
                // However, without more logic, let's assume 'Adjustment' is a delta (positive or negative) for now, 
                // OR strictly 'set to'.
                // Let's implement as a simple add/subtract based on sign for now, or just leave it for user logic refinement.
                // A common pattern for adjustment is to calculate the difference.
                // Let's stick to simple increment for now if positive, decrement if negative implied?
                // Actually, let's treat adjustment as a direct setting for now? No, that breaks the history log logic if quantity isn't the delta.
                // Let's assume quantity is the DELTA.
                $ingredient->increment('current_stock', $quantity);
                break;
        }
    }
}
