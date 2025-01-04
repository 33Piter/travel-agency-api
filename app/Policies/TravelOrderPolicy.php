<?php

namespace App\Policies;

use App\Models\TravelOrder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TravelOrderPolicy
{
    public function view(User $user, TravelOrder $travelOrder): Response
    {
        return $travelOrder->user_id === $user->id
            ? Response::allow()
            : Response::deny('You are not authorized to view this travel order.');
    }

    public function update(User $user, TravelOrder $travelOrder): Response
    {
        return $travelOrder->user_id === $user->id
            ? Response::allow()
            : Response::deny('You are not authorized to update this travel order.');
    }

    public function notify(User $user, TravelOrder $travelOrder): Response
    {
        return $travelOrder->user_id === $user->id
            ? Response::allow()
            : Response::deny('You are not authorized to notify this travel order.');
    }
}
