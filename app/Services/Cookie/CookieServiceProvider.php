<?php

namespace App\Services\Cookie;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Cookie\QueueingFactory;

class CookieServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton(QueueingFactory::class, function ($app) {
            $config = $app->make('config')->get('session');

            return (new CookieJar)->setDefaultPathAndDomain(
                            $config['path'], $config['domain'], $config['secure'], $config['same_site'] ?? null
            );
        });
//        $this->app->singleton('cookie', function ($app) {
//            return app(QueueingFactory::class);
//        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return [QueueingFactory::class, 'cookie'];
    }

    /**
     * Determine if the provider is deferred.
     *
     * @return bool
     */
    public function isDeferred() {
        return true;
    }

}
