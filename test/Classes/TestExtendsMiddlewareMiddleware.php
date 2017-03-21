<?php
namespace Test\Classes;

use DMS\TornadoHttp\Middleware\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestExtendsMiddlewareMiddleware extends Middleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface
    {
        return $next($request, $response);
    }
}
