<?php

declare(strict_types=1);

namespace ArtisanToolbox\Mosaicast;

use ArtisanToolbox\Mosaicast\Actions\ResolveMosaicastPayload;
use ArtisanToolbox\Mosaicast\Auth\SessionBroadcastGuard;
use ArtisanToolbox\Mosaicast\Broadcasting\SessionBroadcastChannel;
use ArtisanToolbox\Mosaicast\Delivery\MosaicastBroadcaster;
use ArtisanToolbox\Mosaicast\Http\MosaicastRequestContext;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class MosaicastServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mosaicast.php', 'mosaicast');

        $this->app->scoped(MosaicastRequestContext::class);

        $this->app->scoped(Mosaicast::class);

        $this->app->singleton(
            SessionBroadcastChannel::class,
            fn (): SessionBroadcastChannel => new SessionBroadcastChannel(
                (string) config('mosaicast.broadcasting.session_channel_key'),
                (string) config('mosaicast.broadcasting.session_channel_prefix'),
            ),
        );

        $guard = (string) config('mosaicast.broadcasting.guard');

        if (config("auth.guards.{$guard}") === null) {
            config()->set("auth.guards.{$guard}", [
                'driver' => 'mosaicast-session',
            ]);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::extend('mosaicast-session', function (Application $app): SessionBroadcastGuard {
            $guard = new SessionBroadcastGuard(
                $app->make('request'),
                $app->make(SessionBroadcastChannel::class),
            );

            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });

        if (class_exists(Inertia::class)) {
            Inertia::share(
                'mosaicastSessionIdentifier',
                Inertia::always(
                    fn (): ?string => resolve(ResolveMosaicastPayload::class)
                        ->sessionIdentifier(resolve(Request::class)),
                ),
            );

            Inertia::share(
                'mosaicast',
                fn (): array => resolve(ResolveMosaicastPayload::class)
                    ->handle(resolve(Request::class)),
            );
        }

        $events = $this->app->make(Dispatcher::class);

        $events->listen(RequestHandled::class, function (RequestHandled $handled): void {
            $context = $this->app->make(MosaicastRequestContext::class);
            $context->initialize($handled->request);
            $target = $context->target();
            $pendingEvents = $context->finalize();

            if ($target === null) {
                return;
            }

            $broadcaster = $this->app->make(MosaicastBroadcaster::class);

            foreach ($pendingEvents as $event) {
                $broadcaster->dispatch($target, $event);
            }
        });

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
    }
}
