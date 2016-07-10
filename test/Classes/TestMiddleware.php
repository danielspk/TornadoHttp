<?php
namespace Test\Classes;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $next($request, $response);
    }
}
