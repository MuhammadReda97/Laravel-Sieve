<?php

namespace RedaLabs\LaravelFilters;

use Illuminate\Support\ServiceProvider;

class LaravelFiltersServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Criteria::class, fn() => new Criteria());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
} 