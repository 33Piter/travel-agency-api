<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTravelOrderRequest;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderRequest;
use App\Http\Resources\TravelOrderResource;
use App\Models\TravelOrder;
use Illuminate\Http\JsonResponse;

class TravelOrderController extends Controller
{
    public function index(IndexTravelOrderRequest $request)
    {
        //
    }

    public function show(TravelOrder $travelOrder): JsonResponse
    {
        if ($travelOrder->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to view this travel order.'], 403);
        }

        return response()->json(new TravelOrderResource($travelOrder));
    }

    public function store(StoreTravelOrderRequest $request): JsonResponse
    {
        $requestData = $request->validated();
        $requestData['user_id'] = auth()->user()->id;
        $travelOrder = TravelOrder::create($requestData);

        return response()->json([
            'message' => 'Travel order created successfully.',
            'data' => new TravelOrderResource($travelOrder),
        ], 201);
    }

    public function update(UpdateTravelOrderRequest $request, TravelOrder $travelOrder)
    {
        //
    }

    public function notify(TravelOrder $travelOrder)
    {
        //
    }
}
