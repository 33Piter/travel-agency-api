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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
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
        Gate::authorize('view', $travelOrder);

        return response()->json(new TravelOrderResource($travelOrder));
    }

    public function store(StoreTravelOrderRequest $request): JsonResponse
    {
        $requestData = array_merge($request->validated(), ['user_id' => auth()->id()]);
        $travelOrder = TravelOrder::create($requestData);

        return $this->successResponse('Travel order created successfully.', new TravelOrderResource($travelOrder), 201);
    }

    public function update(UpdateTravelOrderRequest $request, TravelOrder $travelOrder): JsonResponse
    {
        Gate::authorize('update', $travelOrder);

        $newStatus = $request->input('status');

        if ($travelOrder->status->value === $newStatus) {
            return $this->errorResponse('The travel order status is already set to '.$newStatus.'.');
        }

        $travelOrder->status = $newStatus;
        $travelOrder->save();

        return $this->successResponse('Travel order status updated successfully.', new TravelOrderResource($travelOrder));
    }

    public function notify(TravelOrder $travelOrder): JsonResponse
    {
        Gate::authorize('notify', $travelOrder);

        try {
            Mail::to($travelOrder->applicant_email)->send(new TravelOrderStatusUpdated($travelOrder));
        } catch (\Exception $e) {
            Log::error('Failed to send notification for travel order', [
                'error' => $e->getMessage(),
                'travel_order_id' => $travelOrder->id,
            ]);
            return $this->errorResponse('Failed to send notification.', 500);
        }

        return $this->successResponse('Notification sent successfully.');
    }

    private function successResponse(string $message, $data = null, int $status = 200)
    {
        return response()->json(compact('message', 'data'), $status);
    }

    private function errorResponse(string $message, int $status = 400)
    {
        return response()->json(compact('message'), $status);
    }
}
