<?php

namespace App\Providers;

use App\Repository\Authenticate\AuthRepository;
use App\Repository\Authenticate\AuthRepositoryInterface;
use App\Repository\Order\OrderRepository;
use App\Repository\Order\OrderRepositoryInterface;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
