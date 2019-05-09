<?php

declare(strict_types=1);

namespace Test\Resolver;

use DMS\TornadoHttp\Resolver\ResolverInterface;
use Psr\Http\Server\MiddlewareInterface;

class CustomResolver implements ResolverInterface
{
    public function solve($middleware): MiddlewareInterface
    {
        return new $middleware();
    }
}
