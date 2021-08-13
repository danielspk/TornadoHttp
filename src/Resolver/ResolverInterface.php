<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Resolver;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Middleware Resolver interface.
 */
interface ResolverInterface
{
    /**
     * Solve and/or returns an MiddlewareInterface.
     *
     * @param mixed $middleware Middleware
     */
    public function solve(mixed $middleware): MiddlewareInterface;
}
