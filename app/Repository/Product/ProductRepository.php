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

    public function store(array $data)
    {
        return $this->product->query()->create($data);
    }

    public function update(Product $product, array $data)
    {
        $this->product->query()->update($data);
        return $product->refresh();
    }
}
