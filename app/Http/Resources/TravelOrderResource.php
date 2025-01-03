<?php

namespace App\Http\Resources;

use App\Enums\TravelOrderStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'applicant_name' => $this->applicant_name,
            'applicant_email' => $this->applicant_email,
            'destination' => $this->destination,
            'departure_date' => $this->departure_date,
            'return_date' => $this->return_date,
            'status' => $this->status ?? TravelOrderStatusEnum::REQUESTED->value,
        ];
    }
}
