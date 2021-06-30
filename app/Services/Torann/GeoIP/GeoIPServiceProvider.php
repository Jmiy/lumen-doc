<?php

namespace App\Services\Torann\GeoIP;

use Torann\GeoIP\GeoIPServiceProvider as TorannGeoIPServiceProvider;

class GeoIPServiceProvider extends TorannGeoIPServiceProvider {

    /**
     * Register currency provider.
     *
     * @return void
     */
    public function registerGeoIpService() {
        $this->app->singleton('geoip', function ($app) {
            return new GeoIP(
                    $app->config->get('geoip', []), $app['cache']
            );
        });
    }

}
