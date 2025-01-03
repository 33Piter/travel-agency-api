<?php

namespace App\Models;

use App\Enums\TravelOrderStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TravelOrder extends Model
{
    /** @use HasFactory<\Database\Factories\TravelOrderFactory> */
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
}
