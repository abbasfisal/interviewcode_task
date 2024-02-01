<?php

namespace App\Documents\Product;

/**
 * @OA\Get (
 *     path="/api/products",
 *     tags={"Product"},
 *     summary="List all of Product" ,
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(response=200,description="OK")
 * )
 *
 * @OA\Post(
 *     path="/api/products",
 *     security={{"bearerAuth":{}}},
 *     tags={"Product"},
 *     summary="Create new Product" ,
 *     @OA\RequestBody(required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(required={},
 *                  @OA\Property(
 *                      property="name",
 *                      type="string"
 *                  ),
 *                  @OA\Property(
 *                      property="price",
 *                      type="integer"
 *                  ),
 *                  @OA\Property(
 *                      property="inventory",
 *                      type="integer"
 *                  ),*
 *                  example={"name":"product-One","price":20000 ,"inventory":300 }
 *              )
 *          )
 *     ),
 *     @OA\Response(response=200,description="OK")
 * )
 *
 * @OA\Patch(
 *     path="/api/products/{id}/",
 *     tags={"Product"},
 *     summary="Update a Product" ,
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id",in="path",required=true,description="product id",@OA\Schema(type="string")),
 *     @OA\RequestBody(required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(required={},
 *                  @OA\Property(
 *                      property="name",
 *                      type="string"
 *                  ),
 *                       @OA\Property(
 *                      property="price",
 *                      type="integer"
 *                  ),
 *                    @OA\Property(
 *                      property="inventory",
 *                      type="integer"
 *                  ),
 *                  example={"name":"update-product-one" , "price":34000 , "inventory":400}
 *              )
 *          )
 *     ),
 *     @OA\Response(response=200,description="OK")
 * )
 */
class Product
{

}
