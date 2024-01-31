<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderShowRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Repository\Order\OrderRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class OrderController extends Controller
{
    public function __construct(private readonly OrderRepositoryInterface $orderRepository)
    {
    }


    public function index(): JsonResponse
    {
        $orders = $this->orderRepository->index();
        return Response::json([
            'message' => 'order lists',
            'data'    => OrderResource::collection($orders)
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderRepository->store($request->validated());
        return Response::json([
            'message' => 'created',
            'data'    => new OrderResource($order)
        ]);
    }

    public function show(Order $order, OrderShowRequest $request): JsonResponse
    {
        return Response::json([
            'message' => 'order show',
            'data'    => new OrderResource($order)
        ]);
    }


    public function update(UpdateOrderRequest $request, Order $order)
    {
        //
    }

    public function destroy(Order $order, OrderShowRequest $request): JsonResponse
    {
        $this->orderRepository->destroy($order);
        return Response::json(['message' => 'delete successfully']);
    }
}
