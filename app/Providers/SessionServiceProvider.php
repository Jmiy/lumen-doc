<?php

namespace App\Providers;

//use Illuminate\Support\ServiceProvider;
//use Illuminate\Session\Middleware\StartSession;
use Illuminate\Session\SessionServiceProvider as LumenSessionServiceProvider;

class SessionServiceProvider extends LumenSessionServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $config = app('config');
        $config->set('session.cookie', config('session.cookie') . '_5');
        parent::register();
    }

}
