<?php

namespace App\Enums;

enum TravelOrderStatusEnum: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case CANCELED = 'canceled';
}