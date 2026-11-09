<?php

namespace App\Providers;

use App\Contracts\SmsGateway;
use App\Http\Middleware\EnsureAdmin;
use App\Services\Sms\HttpSmsGateway;
use App\Services\Sms\LogSmsGateway;
use App\Support\Seo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(Seo::class);

        $this->app->singleton(SmsGateway::class, function () {
            $sms = config('bandhan.sms');

            return $sms['driver'] === 'http'
                ? new HttpSmsGateway((string) $sms['api_url'], (string) $sms['api_key'], (string) $sms['sender_id'])
                : new LogSmsGateway;
        });
    }

    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        // The stylesheet is already render-blocking in <head>; a preload hint for it only
        // triggers "preloaded but not used" warnings after wire:navigate swaps pages.
        Vite::usePreloadTagAttributes(false);

        // Re-check the admin role on every Livewire action from an admin page, not only on page load,
        // so someone demoted mid-session cannot keep using an open admin tab.
        Livewire::addPersistentMiddleware([EnsureAdmin::class]);
    }
}
