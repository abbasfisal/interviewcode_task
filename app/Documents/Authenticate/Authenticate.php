<?php

namespace App\Documents\Authenticate;

/**
 * @OA\Post(
 *     path="/api/auth/register",
 *     tags={"Authenticate"},
 *     summary="Register new User" ,
 *     @OA\RequestBody(required=true,
 *          @OA\MediaType(
 *              mediaType="application/json",
 *              @OA\Schema(required={},
 *                  @OA\Property(
 *                      property="name",
 *                      type="string"
 *                  ),
 *                  @OA\Property(
 *                      property="email",
 *                      type="string"
 *                  ),
 *                  @OA\Property(
 *                      property="password",
 *                      type="string"
 *                  ),*
 *                  @OA\Property(
 *                      property="password_confirmation",
 *                      type="string"
 *                  ),
 *
 *                  example={"name":"abbas","email":"abbas@gmail.com" ,"password":"password","password_confirmation":"password" }
 *              )
 *          )
 *     ),
 *     @OA\Response(response=200,description="OK")
 * )
 */
class Authenticate
{
}
