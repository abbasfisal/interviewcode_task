<?php

namespace App\Repository\Order;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderRepository implements OrderRepositoryInterface
{
    public function index(): Collection|array
    {
        return Order::query()
            ->where('user_id', Auth::id())
            ->get();
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    public function store(array $data)
    {


        $orderProducts = array_column($data['products'], null, 'product_id');

        $products = Product::query()->whereIn('_id', array_keys($orderProducts))->lockForUpdate()->get();

        $total_price = $this->checkOutOfStock($products, $orderProducts);

        return $this->updateProductAndCreateOrder($products, $orderProducts, $total_price);

    }

    /**
     * @throws \Exception
     */
    public function update(Order $order, array $data): Order
    {
        list($orderProducts, $productsToRemove, $mergedProducts) = $this->productMap($data['products'], $order);

        DB::beginTransaction();

        $mergedProductIds = $this->restoreInventoryOnOrderCancel($productsToRemove, $mergedProducts);
        $products = Product::query()->whereIn('_id', $mergedProductIds)->lockForUpdate()->get(); //get product by ids which is sent from frontEnd
        $order =$this->calculateProductInventoryAndOrderProducts($order, $products, $mergedProducts, $orderProducts);

        DB::commit();

        return $order->refresh();
    }

    public function destroy(Order $order)
    {
        DB::beginTransaction();
        try {
            foreach ($order->products as $item) {
                /** @var Product $product */
                $product = Product::query()->where('_id', $item['product_id'])->first();

                $product->inventory += $item['count'];
                $product->save();
            }
            $order->delete();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw new BadRequestHttpException($exception->getMessage());
        }

    }


    /**
     * @param Collection $products
     * @param array $orderProducts
     * @return int
     * @throws ValidationException
     */
    public function checkOutOfStock(Collection $products, array $orderProducts): int
    {
        $total_price = 0;
        /** @var Product $item */
        foreach ($products as $item) {
            if (isset($orderProducts[$item->_id])) {

                $orderCount = $orderProducts[$item->_id]['count'];
                $calculateInventory = $item->inventory - $orderCount;

                if ($calculateInventory <= 0) {
                    throw ValidationException::withMessages(['product' => __('validation.out_of_stock'), ['data' => $item->_id]]);
                }
                $total_price += $item->price * $orderCount;
            }
        }
        return $total_price;
    }

    /**
     * @param Collection|array $products
     * @param array $orderProducts
     * @param int $total_price
     * @return \Illuminate\Database\Eloquent\Builder|Model
     * @throws \Exception
     */
    public function updateProductAndCreateOrder(Collection|array $products, array $orderProducts, int $total_price): \Illuminate\Database\Eloquent\Builder|Model
    {
        DB::beginTransaction();
        try {
            $product_data = [];
            foreach ($products as $item) {

                if (isset($orderProducts[$item->_id])) {

                    $orderCount = $orderProducts[$item->_id]['count'];
                    $calculateInventory = $item->inventory - $orderCount;
                    $item->update(['inventory' => $calculateInventory]);

                    $product_data[] = [
                        'product_id' => $item->_id,
                        'name'       => $item->name,
                        'count'      => $orderCount,
                        'price'      => $item->price
                    ];
                }
            }

            $order = Order::query()->create([
                'user_id'     => Auth::id(),
                'products'    => $product_data,
                'total_price' => $total_price
            ]);

            DB::commit();
            return $order;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param $products1
     * @param Order $order
     * @return array
     */
    public function productMap($products1, Order $order): array
    {
        $productMap = array_column($products1, null, 'product_id');

        $orderProducts = array_column($order->products, null, 'product_id');

        $productsToRemove = array_diff_key($orderProducts, $productMap);

        $mergedProducts = array_merge($orderProducts, $productMap);
        return array($orderProducts, $productsToRemove, $mergedProducts);
    }


    public function calculateProductInventoryAndOrderProducts(Order $order, Collection|array $products, array $mergedProducts, array $orderProducts): Order
    {
        $updateOrderProducts = [];
        $totalPrice = 0;

        /** @var Product $product */
        foreach ($products as $product) {

            $sentProductCount = $mergedProducts[$product['_id']]['count'];

            if (isset($orderProducts[$product['_id']])) {

                $orderProductCount = $orderProducts[$product['_id']]['count'];
                if ($orderProductCount != $sentProductCount) {
                    $calculateCount = $orderProductCount - $sentProductCount;
                    $newInventoryCount = $product->inventory + ($calculateCount);
                    if ($newInventoryCount <= 0) {
                        throw new \Exception('Cannot update operation, inventory is zero');
                    }
                    $product->inventory = $newInventoryCount;
                    $product->save();

                }
            } else {
                $product->inventory = $product->inventory - ($sentProductCount);
                $product->save();
            }

            $updateOrderProducts[] = [
                'product_id' => $product->_id,
                'name'       => $product->name,
                'count'      => $sentProductCount,
                'price'      => $product->price
            ];
            $totalPrice += $sentProductCount * $product->price;
        }
        $order->update([
            'products'    => $updateOrderProducts,
            'total_price' => $totalPrice
        ]);

        return $order->refresh();
    }

    /**
     * @param array $productsToRemove
     * @param array $mergedProducts
     * @return array
     */
    public function restoreInventoryOnOrderCancel(array $productsToRemove, array $mergedProducts): array
    {
        foreach ($productsToRemove as $dif) {
            $product = Product::query()
                ->where('_id', $dif['product_id'])
                ->lockForUpdate()->first();
            $product->update(['inventory' => $product->inventory + $dif['count']]);

            unset($mergedProducts[$dif['product_id']]);
        }

        return array_keys($mergedProducts);
    }
}
