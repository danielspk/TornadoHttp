<?php
namespace Test\Resolver;

use DMS\TornadoHttp\Resolver\ResolverInterface;

class CustomResolver implements ResolverInterface
{
    public function solve($middleware)
    {
        return new $middleware;
    }
}
