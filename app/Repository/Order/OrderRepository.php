<?php

namespace App\Repository\Order;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderRepository implements OrderRepositoryInterface
{
    public function __construct(private readonly Order $order)
    {
    }

    public function index(): Collection|array
    {

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

    public function destory(Order $product)
    {

    }
}
//
//$prodcuts = [ ['id'=>1 , 'inventory'=>20 , 'price'=>20000] ,['id'=>2 , 'inventory'=>32 , 'price'=>1000]];
//$orders = [ ['id'=>1 , 'count'=>3 ] , ['id'=>2 , 'count'=>4]]   ;
//
//$products = [
//    ['id' => 1, 'inventory' => 20, 'price' => 20000],
//    ['id' => 2, 'inventory' => 32, 'price' => 1000]
//];
//
//$orders = [
//    ['id' => 1, 'count' => 3],
//    ['id' => 2, 'count' => 4]
//];
//
//// Create associative arrays based on 'id' for products and orders
//$productMap = array_column($products, null, 'id');
//$orderMap = array_column($orders, null, 'id');
//
//// Merge the arrays based on 'id'
//$mergedArray = [];
//foreach ($productMap as $productId => $productInfo) {
//    if (isset($orderMap[$productId])) {
//        $mergedArray[] = array_merge($productInfo, $orderMap[$productId]);
//    }
//}
//
//// Print the merged array
//print_r($mergedArray);
