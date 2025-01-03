<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexTravelOrderRequest;
use App\Http\Requests\StoreTravelOrderRequest;
use App\Http\Requests\UpdateTravelOrderRequest;
use App\Http\Resources\TravelOrderResource;
use App\Mail\TravelOrderStatusUpdated;
use App\Models\TravelOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Mail;

class TravelOrderController extends Controller
{
    public function index(IndexTravelOrderRequest $request): AnonymousResourceCollection|JsonResponse
    {
        $filters = $request->validated();

        $travelOrders = auth()->user()->travelOrders()->filter($filters)->paginate(10);

        if ($travelOrders->isEmpty()) {
            return response()->json(['message' => 'No travel orders found matching the provided criteria.'], 404);
        }

        return TravelOrderResource::collection($travelOrders);
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

    public function update(UpdateTravelOrderRequest $request, TravelOrder $travelOrder): JsonResponse
    {
        if ($travelOrder->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to update this travel order.'], 403);
        }

        $newStatus = $request->input('status');

        if ($travelOrder->status->value === $newStatus) {
            return response()->json([
                'message' => 'The travel order status is already set to '.$newStatus.'.',
            ], 400);
        }

        $travelOrder->status = $newStatus;
        $travelOrder->save();

        return response()->json([
            'message' => 'Travel order status updated successfully.',
            'travel_order' => $travelOrder,
        ]);
    }

    public function notify(TravelOrder $travelOrder): JsonResponse
    {
        if ($travelOrder->user_id !== auth()->id()) {
            return response()->json(['message' => 'You are not authorized to notify this travel order.'], 403);
        }

        try {
            Mail::to($travelOrder->applicant_email)->send(new TravelOrderStatusUpdated($travelOrder));
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to send notification.'], 500);
        }

        return response()->json(['message' => 'Notification sent successfully.']);
    }
}
