<?php

namespace App\Repository\Order;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly Order $order)
    {
    }

    public function index(): Collection|array
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->get();
    }

    public function store(array $data)
    {
        //todo: active replica for transaction
        //DB::beginTransaction();
        $product_ids = array_column($data['products'], 'product_id');
        $p = Product::query()->whereIn('_id', $product_ids)->lockForUpdate()->get()->toArray();

        $productMap = array_column($p, null, '_id');
        $orderMap = array_column($data['products'], null, 'product_id');

        // $mergedArray = [];
        $total_price = 0;

        foreach ($productMap as $productId => $productInfo) {
            if (isset($orderMap[$productId])) {

                $calculateInventory = $productInfo['inventory'] - $orderMap[$productId]['count'];
                if ($calculateInventory <= 0) {
                    throw ValidationException::withMessages(['product' => __('validation.out_of_stock'), ['data' => $productInfo]]);
                }
                $total_price += $productInfo['price'] * $orderMap[$productId]['count'];
                //$mergedArray[] = array_merge($productInfo, $orderMap[$productId]);
            }
        }

        try {

            foreach ($productMap as $productId => $productInfo) {
                if (isset($orderMap[$productId])) {
                    Product::query()->where('_id', $productId)
                        ->update(['inventory' => $productInfo['inventory'] - $orderMap[$productId]['count']]);
                }
            }
            $order = Order::query()->create([
                'user_id'     => Auth::id(),
                'products'    => $data['products'],
                'total_price' => $total_price
            ]);


            //DB::commit();
            return $order;
        } catch (\Exception $exception) {
            //  DB::rollBack();
            throw $exception;
        }


        // dd($total_price, $data['products']);


    }

    public function update(Order $product, array $data)
    {
    }

    public function destroy(Order $order)
    {
        //todo: solve transaction problem
        // DB::beginTransaction();
        try {
            foreach ($order->products as $item) {
                /** @var Product $product */
                $product = Product::query()->where('_id', $item['product_id'])->first();

                $product->inventory += $item['count'];
                $product->save();
            }
            $order->delete();
            // DB::commit();
        } catch (\Exception $exception) {
            // DB::rollBack();
            throw new BadRequestHttpException($exception->getMessage());
        }

    }
}
