<?php

namespace App\Http\Middleware;

use TusPhp\Request;
use TusPhp\Response;
use TusPhp\Middleware\TusMiddleware;

class TusRequestMiddleware implements TusMiddleware
{

    public function handle(Request $request, Response $response)
    {
        $request->getRequest()->setMethod(request()->method());
        $request->getRequest()->server->set('REQUEST_URI', request()->path());
        foreach (request()->headers as $header => $value) {
            $request->getRequest()->headers->set($header, $value);
        }
    }
}
