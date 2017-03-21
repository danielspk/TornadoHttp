<?php
namespace Test\Classes;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestChildExtendsMiddlewareMiddleware extends TestExtendsMiddlewareMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface
    {
        return $next($request, $response);
    }
}
