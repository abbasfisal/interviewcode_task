<?php

namespace App\Repository\Product;

use App\Models\Product;

interface ProductRepositoryInterface
{
    public function index();

    public function store(array $data);

    public function update(Product $product, array $data);
}
