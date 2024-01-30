<?php

namespace App\Repository\Product;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(private readonly Product $product)
    {
    }

    public function index(): Collection|array
    {
        return $this->product->query()->get();
    }
}
