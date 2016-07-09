<?php
namespace Test\Classes;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestChildTraitMiddleware extends TestTraitMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $next($request, $response);
    }
}
