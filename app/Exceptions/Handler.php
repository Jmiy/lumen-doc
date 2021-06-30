<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Response;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Debug\ExceptionHandler as SymfonyExceptionHandler;
use Illuminate\Http\JsonResponse;
use App\Util\Response as UtilResponse;
use App\Util\Constant;
use App\Services\Monitor\MonitorServiceManager;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];
    public static $debug;

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception) {

        try {
            $logger = app('Psr\Log\LoggerInterface');

            $exceptionData = static::getMessage($exception);
            $logger->error($exceptionData);
            //parent::report($exception);
            //添加系统异常监控
            $exceptionName = '系统异常：';
            $message = data_get($exceptionData, 'message', '');
            $code = data_get($exceptionData, 'exception_code') ? data_get($exceptionData, 'exception_code') : (data_get($exceptionData, 'http_code') ? data_get($exceptionData, 'http_code') : -101);
            $parameters = [$exceptionName, $message, $code, data_get($exceptionData, 'file'), data_get($exceptionData, 'line'), $exceptionData];
            MonitorServiceManager::handle('Ali', 'Ding', 'report', $parameters);
        } catch (Exception $ex) {
            throw $exception; // throw the original exception
        }
    }

    /**
     * @param Exception $exception
     * @return array
     */
    public static function getMessage(Exception $exception, $debug = true) {

        static::$debug = $debug;
        $fe = FlattenException::create($exception);

        $responseData = static::convertExceptionToArray($fe);

        $traces = data_get($responseData, Constant::RESPONSE_DATA_KEY, []);
        $depth = config('app.debug_depth', 3);
        data_set($traces, '0.trace', array_slice(data_get($traces, '0.trace', []), 0, $depth));

        return [
            'exception_code' => $fe->getCode(),
            "http_code" => $fe->getStatusCode(),
            'message' => data_get($responseData, Constant::RESPONSE_MSG_KEY, ''),
            'type' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack-trace' => $depth === 0 ? $depth : data_get($traces, 0, []),
        ];
    }

    /**
     * 获取响应数据
     * @param  \Illuminate\Http\Request  $request
     * @param \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getResponseData($request, $response) {
        if ($response instanceof JsonResponse) {
            $response = $response->getData(true);
        }

        return $request->expectsJson() ? UtilResponse::json(data_get($response, Constant::RESPONSE_DATA_KEY, $response), data_get($response, Constant::RESPONSE_CODE_KEY, 1), data_get($response, Constant::RESPONSE_MSG_KEY, '')) : $response;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $e) {//
        if ($e instanceof HttpResponseException) {
            return $this->getResponseData($request, $e->getResponse());
        } elseif ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        } elseif ($e instanceof AuthorizationException) {
            $e = new HttpException(403, $e->getMessage());
        } elseif ($e instanceof ValidationException && $e->getResponse()) {
            return $this->getResponseData($request, $e->getResponse());
        }

        static::$debug = config('app.debug');

        return $request->expectsJson() ? $this->prepareJsonResponse($request, $e) : $this->prepareResponse($request, $e);
    }

    /**
     * Prepare a JSON response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function prepareJsonResponse($request, $exception) {// Exception FlattenException
        $fe = FlattenException::create($exception);

        $responseData = static::convertExceptionToArray($fe);

        $depth = config('app.debug_depth', 3);
        data_set($responseData, 'data.0.trace', array_slice(data_get($responseData, 'data.0.trace', []), 0, $depth));

        $data = data_get($responseData, 'data.0', []);
        $code = data_get($responseData, Constant::RESPONSE_CODE_KEY, 0);
        $msg = data_get($responseData, Constant::RESPONSE_MSG_KEY, '');
        $status = $fe->getStatusCode();
        $headers = $fe->getHeaders();
        $options = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES;

        return UtilResponse::json($data, $code, $msg, true, $status, $headers, $options);
    }

    /**
     * Convert the given exception to an array.
     *
     * @param  \Exception  $exception
     * @return array
     */
    public static function convertExceptionToArray($exception) {

        $msgKey = 'message';
        $traceKey = 'trace';
        switch ($exception->getStatusCode()) {
            case 404:
                $title = 'Sorry, the page you are looking for could not be found.';
                break;
            default:
                $title = static::$debug ? $exception->getMessage() : 'Whoops, looks like something went wrong.';
        }

        $traces = [];
        try {
            $count = \count($exception->getAllPrevious());
            $total = $count + 1;
            foreach ($exception->toArray() as $position => $e) {
                $ind = $count - $position + 1;
                $traces[] = [
                    'ind' => $ind,
                    'total' => $total,
                    'class' => data_get($e, 'class', ''),
                    $msgKey => data_get($e, $msgKey, ''),
                    $traceKey => $e[$traceKey],
                ];
            }
        } catch (\Exception $e) {
            // something nasty happened and we cannot throw an exception anymore
            if (static::$debug) {
                $e = FlattenException::create($e);
                $traces[] = [
                    'class' => $e->getClass(),
                    $msgKey => $e->getMessage(),
                    $traceKey => $e->toArray(),
                ];
            } else {
                $title = 'Whoops, looks like something went wrong.';
                $traces[] = [
                    $msgKey => $title,
                ];
            }
        }
        return [
            Constant::RESPONSE_CODE_KEY => $exception->getCode(),
            Constant::RESPONSE_MSG_KEY => $title,
            Constant::RESPONSE_DATA_KEY => static::$debug ? $traces : [],
        ];
    }

    /**
     * Render an exception to the console.
     *
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @param  \Exception  $e
     * @return void
     */
    public function renderForConsole($output, Exception $e) {
        (new ConsoleApplication)->renderException($e, $output);
    }

    /**
     * Prepare a response for the given exception.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception $e
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareResponse($request, Exception $e) {

        $fe = FlattenException::create($e);

        $handler = new SymfonyExceptionHandler(env('APP_DEBUG', config('app.debug', false)));

        $decorated = $this->decorate($handler->getContent($fe), $handler->getStylesheet($fe));

        $response = new Response($decorated, $fe->getStatusCode(), $fe->getHeaders());

        $response->exception = $e;

        return $response;
    }

}
