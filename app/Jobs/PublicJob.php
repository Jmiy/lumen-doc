<?php

namespace App\Jobs;

use App\Services\LogService;
use Exception;
use App\Exceptions\Handler as ExceptionHandler;
use App\Util\Constant;
use Illuminate\Http\Request as IlluminateRequest;

class PublicJob extends Job {

    public $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data = null) {
        $this->data = $data;
    }

    /**
     * @param BaseJob $job
     * @param mixed   $data
     */
    public function fire($job, $data) {

//        $payload = $job->payload()['job'];
//        $jobClass = new $payload();
//        $jobClass->job = $job;
//        $jobClass->data = $data;
//        /** @noinspection PhpUndefinedMethodInspection */
//        $jobClass->handle();

        $this->job = $job; //必须设置当前job，用于执行完成handle以后删除当前job 防止job重复执行
        $this->data = $data;
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        $service = data_get($this->data, Constant::SERVICE_KEY, '');
        $method = data_get($this->data, Constant::METHOD_KEY, '');
        $parameters = data_get($this->data, Constant::PARAMETERS_KEY, []);

        if ($service && $method && method_exists($service, $method)) {
            try {

//                // Initialize laravel request
//                IlluminateRequest::enableHttpMethodParameterOverride();
//                $requestData = data_get($this->data, Constant::REQUEST_DATA_KEY, []);
//                $request = IlluminateRequest::createFromBase(new \Symfony\Component\HttpFoundation\Request([], $requestData, [], [], [], [], []));
//                app()->instance('request', $request);
//                dump($request->all());
//                app('request') = $request;
//
//                $request = app('request');
//                $requestData = data_get($this->data, Constant::REQUEST_DATA_KEY, []);
//                foreach ($requestData as $key => $value) {
//                    $request->offsetSet($key, $value);
//                }

                // Initialize laravel request
                $requestData = data_get($this->data, Constant::REQUEST_DATA_KEY, []);
                IlluminateRequest::enableHttpMethodParameterOverride();
                $request = IlluminateRequest::createFromBase(new \Symfony\Component\HttpFoundation\Request($requestData, $requestData, $requestData));
                app('app')->instance('request', $request);

                call([$service, $method], $parameters);//兼容各种调用 $service::{$method}(...$parameters);

            } catch (Exception $exc) {
                $parameters = [
                    'parameters' => $this->data,
                    'exc' => ExceptionHandler::getMessage($exc),
                ];
                LogService::addSystemLog('error', $service, $method, 'PublicJob--执行失败', $parameters); //添加系统日志
            }
        }

        $this->delete(); //删除当前job 防止job重复执行
    }

}
