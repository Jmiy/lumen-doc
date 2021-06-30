<?php

namespace App\Hhxsv5\Console;

use Hhxsv5\LaravelS\Console\Portal as HhxsvPortal;
use App\Hhxsv5\LaravelS;

class Portal extends HhxsvPortal
{

    public function start()
    {
        if (!extension_loaded('swoole')) {
            $this->error('LaravelS requires swoole extension, try to `pecl install swoole` and `php --ri swoole`.');
            return 1;
        }

        // Generate conf file storage/laravels.conf
        $options = $this->input->getOptions();
        if (isset($options['env']) && $options['env'] !== '') {
            $_SERVER['LARAVEL_ENV'] = $_ENV['LARAVEL_ENV'] = $options['env'];
        }
        if (isset($options['x-version']) && $options['x-version'] !== '') {
            $_SERVER['X_VERSION'] = $_ENV['X_VERSION'] = $options['x-version'];
        }

        // Load Apollo configurations to .env file
        if (!empty($options['enable-apollo'])) {
            $this->loadApollo($options);
        }

        $passOptionStr = '';
        $passOptions = ['daemonize', 'ignore', 'x-version'];
        foreach ($passOptions as $key) {
            if (!isset($options[$key])) {
                continue;
            }
            $value = $options[$key];
            if ($value === false) {
                continue;
            }
            $passOptionStr .= sprintf('--%s%s ', $key, is_bool($value) ? '' : ('=' . $value));
        }
        $statusCode = $this->runArtisanCommand(trim('laravels config ' . $passOptionStr));
        if ($statusCode !== 0) {
            return $statusCode;
        }

        // Here we go...
        $config = $this->getConfig();

        if (!$config['server']['ignore_check_pid'] && file_exists($config['server']['swoole']['pid_file'])) {
            $pid = (int)file_get_contents($config['server']['swoole']['pid_file']);
            if ($pid > 0 && self::kill($pid, 0)) {
                $this->warning(sprintf('Swoole[PID=%d] is already running.', $pid));
                return 1;
            }
        }

        if ($config['server']['swoole']['daemonize']) {
            $this->trace('Swoole is running in daemon mode, see "ps -ef|grep laravels".');
        } else {
            $this->trace('Swoole is running, press Ctrl+C to quit.');
        }

        (new LaravelS($config['server'], $config['laravel']))->run();

        return 0;
    }
}
