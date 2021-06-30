<?php

namespace App\Services\Torann\GeoIP;

use App\Util\FunctionHelper;
use Torann\GeoIP\GeoIP as TorannGeoIP;
use Illuminate\Support\Arr;

class GeoIP extends TorannGeoIP {

    /**
     * Set configuration value.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setConfig($key, $value = null) {
        data_set($this->config, $key, $value);
        return $this;
    }

    /**
     * Get service instance.
     *
     * @return \Torann\GeoIP\Contracts\ServiceInterface
     * @throws Exception
     */
    public function getService() {

        $service = $this->config('service');
        if (data_get($this->service, $service, null) === null) {
            // Get service configuration
            $config = $this->config('services.' . $service, []);

            // Get service class
            $class = Arr::pull($config, 'class');

            // Sanity check
            if ($class === null) {
                throw new Exception('The GeoIP service is not valid.');
            }

            // Create service instance
            data_set($this->service, $service, new $class($config));
        }

        return data_get($this->service, $service, null);
    }

    public function setService($service) {
        $this->service = $service;

        return $this;
    }

    /**
     * Get the client IP address.
     *
     * @return string
     */
    public function getClientIP()
    {
        return FunctionHelper::getClientIP();
    }

}
