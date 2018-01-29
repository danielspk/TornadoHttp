<?php

namespace Test\Classes;

use DMS\TornadoHttp\Middleware\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TestExtendsMiddlewareNotOverrideProcessMiddleware extends Middleware
{
}
