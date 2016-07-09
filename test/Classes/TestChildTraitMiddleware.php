<?php
namespace Classes;

use Classes\TestTraitMiddleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestChildTraitMiddleware extends TestTraitMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $response;
    }
}
