<?php

namespace App\Models;

use App\Enums\TravelOrderStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'applicant_name',
        'applicant_email',
        'destination',
        'departure_date',
        'return_date',
    ];

    protected $casts = [
        'status' => TravelOrderStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['departure_date'] ?? null, fn ($query, $date) => $query->where('departure_date', $date))
            ->when($filters['return_date'] ?? null, fn ($query, $date) => $query->where('return_date', $date))
            ->when($filters['destination'] ?? null, fn ($query, $destination) => $query->where('destination', 'like', "%{$destination}%"))
            ->when(
                isset($filters['departure_date_start'], $filters['departure_date_end']),
                fn ($query) => $query->whereBetween('departure_date', [$filters['departure_date_start'], $filters['departure_date_end']])
            )
            ->when(
                isset($filters['return_date_start'], $filters['return_date_end']),
                fn ($query) => $query->whereBetween('return_date', [$filters['return_date_start'], $filters['return_date_end']])
            )
            ->when(
                isset($filters['date_range_start'], $filters['date_range_end']),
                fn ($query) => $query->where(function ($query) use ($filters) {
                    $query->whereBetween('departure_date', [$filters['date_range_start'], $filters['date_range_end']])
                        ->orWhereBetween('return_date', [$filters['date_range_start'], $filters['date_range_end']])
                        ->orWhere(function ($query) use ($filters) {
                            $query->where('departure_date', '<=', $filters['date_range_end'])
                                ->where('return_date', '>=', $filters['date_range_start']);
                        });
                })
            );
    }
}
