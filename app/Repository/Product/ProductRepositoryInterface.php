<?php

namespace App\Repository\Product;

interface ProductRepositoryInterface
{
    public function index();

    public function store(array $data);
}
