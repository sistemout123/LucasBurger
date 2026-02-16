<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')
            ->withPivot(['quantity', 'correction_factor', 'cooking_factor'])
            ->withTimestamps();
    }

    public function productIngredients()
    {
        return $this->hasMany(ProductIngredient::class);
    }
}
