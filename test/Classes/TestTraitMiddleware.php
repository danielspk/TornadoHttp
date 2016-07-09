<?php
namespace Classes;

use DMS\TornadoHttp\Container\ContainerTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TestTraitMiddleware
{
    use ContainerTrait;

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        return $response;
    }
}
