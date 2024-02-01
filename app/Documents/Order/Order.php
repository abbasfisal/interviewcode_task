<?php

namespace App\Documents\Order;

/**
 * @OA\Get (
 *     path="/api/orders",
 *     tags={"Order"},
 *     summary="List all of Order" ,
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200,description="OK")
 * )
 *
 * @OA\Post(
 *     path="/api/orders",
 *     security={{"bearerAuth":{}}},
 *     tags={"Order"},
 *     summary="Create new Order" ,
 *     @OA\RequestBody(required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(required={},
 *                 example={"products":{ {"product_id":"23423423423" , "count":3 }} }
 *              )
 *          )
 *     ),
 *     @OA\Response(response=200,description="OK")
 * )
 *
 * @OA\Patch(
 *     path="/api/orders/{id}",
 *     tags={"Order"},
 *     summary="Update a Order" ,
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id",in="path",required=true,description="Order id",@OA\Schema(type="string")),
 *     @OA\RequestBody(required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(required={},
 *                  example={ "products": { {"product_id":"324234234234" , "count":3} } }
 *              )
 *          )
 *     ),
 *     @OA\Response(response=200,description="OK")
 * )
 *
 * @OA\Delete(
 *     path="/api/orders/{id}",
 *     tags={"Order"},
 *     summary="Delete a Order" ,
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id",in="path",required=true,description="Order id",@OA\Schema(type="string")),
 *     @OA\Response(response=200,description="OK")
 * )
 *
 * @OA\Get(
 *     path="/api/orders/{id}",
 *     tags={"Order"},
 *     summary="Show Order by id" ,
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id",in="path",required=true,description="order id",@OA\Schema(type="string")),
 *     @OA\Response(response=200,description="OK")
 * )
 */
class Order
{
}
