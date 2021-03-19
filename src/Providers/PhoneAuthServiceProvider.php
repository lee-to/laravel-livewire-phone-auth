<?php

namespace Leeto\PhoneAuth\Providers;

use Leeto\PhoneAuth\Livewire\PhoneVerification;
use Leeto\PhoneAuth\SmsBox;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

class PhoneAuthServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    protected $namespace = "phone_auth";

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('sms', function ($app) {
            return (new SmsBox())->smsService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $path = __DIR__ . "/..";

        /* Config */
        $this->publishes([
            $path . '/config/' . $this->namespace . '.php' => config_path($this->namespace . '.php'),
        ]);

        /* Views */
        $this->loadViewsFrom($path . '/views', $this->namespace);

        $this->publishes([
            $path . '/views' => resource_path('views/vendor/' . $this->namespace),
        ]);

        /* Translates */

        $this->loadTranslationsFrom($path . '/lang', $this->namespace);

        $this->publishes([
            $path . '/lang' => resource_path('lang/vendor/' . $this->namespace),
        ]);

        Livewire::component('phone-verification', PhoneVerification::class);

        /* Migrations */
        $this->loadMigrationsFrom($path . '/database/migrations');

        Str::macro('phoneNumber', function ($string) {
            return preg_replace('/^8{1}/', '7', preg_replace('/[^0-9]/', '', $string));
        });
    }
}
