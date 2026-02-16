<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnitOfMeasure extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'acronym'];

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }
}
