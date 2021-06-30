<?php

namespace App\Hhxsv5\Illuminate;

use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Hhxsv5\LaravelS\Illuminate\Laravel as HhxsvLaravel;

class Laravel extends HhxsvLaravel
{

//    public function handleDynamic(IlluminateRequest $request)
//    {
//        ob_start();
//        dump(__METHOD__);
//        if ($this->isLumen()) {
//            $response = $this->currentApp->dispatch($request);
//            if ($response instanceof SymfonyResponse) {
//                $content = $response->getContent();
//            } else {
//                $content = (string)$response;
//            }
//
//            $this->reflectionApp->callTerminableMiddleware($response);
//        } else {
//            $response = $this->kernel->handle($request);
//            $content = $response->getContent();
//            $this->kernel->terminate($request, $response);
//        }
//
//        dump($content,ob_get_contents());
//
//        // prefer content in response, secondly ob
//        if (!($response instanceof StreamedResponse) && strlen($content) === 0 && ob_get_length() > 0) {
//            $response->setContent(ob_get_contents());
//        }
//
//        ob_end_clean();
//
//        return $response;
//    }
}
