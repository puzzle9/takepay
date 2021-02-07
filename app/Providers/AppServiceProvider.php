<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;
        if ($app->environment('local')) {
            $this->app->register(\Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);
        }

        $app->register(\Overtrue\LaravelWeChat\ServiceProvider::class);
        $app->register(\Yansongda\LaravelPay\PayServiceProvider::class);
    }
}
