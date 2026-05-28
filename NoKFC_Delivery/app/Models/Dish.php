<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'description',
    'price',
    'is_active',
    'image_path',
    'preparation_time',
])]
class Dish extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'preparation_time' => 'integer',
        ];
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('amount');
    }


    public function scopeAvailableForOrder(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereDoesntHave('ingredients', function (Builder $q) {
                $q->whereColumn('ingredients.quantity', '<', 'dish_ingredient.amount');
            });
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    protected $appends = ['image_url'];
}