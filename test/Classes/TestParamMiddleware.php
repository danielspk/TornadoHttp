<?php
namespace Test\Classes;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestParamMiddleware
{
    public function __construct($array)
    {}

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $response;
    }
}
