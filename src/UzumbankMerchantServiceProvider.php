<?php

namespace Inwebuz\UzumbankMerchant;

use Illuminate\Support\ServiceProvider;

class UzumbankMerchantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish package migrations
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Publish migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Migrations' => database_path('migrations')
            ], 'migrations');
        }

        // Load package routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Publish package config file
        $this->publishes([
            __DIR__ . '/../config/uzumbankmerchant.php' => config_path('uzumbankmerchant.php'),
        ], 'uzumbankmerchant');

        // Publish views, config, etc., if needed
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        // Register any bindings or services
    }
}
