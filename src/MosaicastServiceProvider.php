<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast;

use ArtisanToolbox\Mosaicast\Console\Commands\MosaicastCommand;
use Illuminate\Support\ServiceProvider;

class MosaicastServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mosaicast.php', 'mosaicast');

        $this->app->singleton(Mosaicast::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/mosaicast.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'mosaicast');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'mosaicast');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/mosaicast.php' => config_path('mosaicast.php'),
        ], ['mosaicast', 'mosaicast-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/mosaicast'),
        ], ['mosaicast', 'mosaicast-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/mosaicast'),
        ], ['mosaicast', 'mosaicast-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/mosaicast'),
        ], ['mosaicast', 'mosaicast-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['mosaicast', 'mosaicast-migrations']);

        $this->commands([
            MosaicastCommand::class,
        ]);
    }
}
