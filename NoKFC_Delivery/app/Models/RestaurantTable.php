<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'restaurant_id',
    'table_number',
    'seats',
    'is_active',
])]
class RestaurantTable extends Model
{
    use HasFactory;

    public const MIN_SEATS = 2;
    public const MAX_SEATS = 6;

    protected function casts(): array
    {
        return [
            'seats' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Check if table is occupied for the given date and time range.
     */
    public function isOccupied(string $date, string $startTime, string $endTime, ?int $excludeReservationId = null): bool
    {
        return $this->reservations()
            ->where('reservation_date', $date)
            ->where('status', '!=', Reservation::STATUS_CANCELLED)
            ->when($excludeReservationId, fn($q) => $q->where('id', '!=', $excludeReservationId))
            ->where(function ($query) use ($startTime, $endTime) {
                // Two intervals [A_start, A_end] and [B_start, B_end] overlap if:
                // A_start < B_end AND A_end > B_start
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();
    }
}
