<?php

namespace App\Providers;

use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Customer\Repositories\CustomerRepository;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Infrastructure\Catalog\Repositories\EloquentProductRepository;
use App\Infrastructure\Ordering\Repositories\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
        // CustomerRepository binding can be added when implementation is created
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
