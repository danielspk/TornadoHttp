<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Resolver;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Middleware Resolver interface.
 *
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 *
 * @see http://tornadohttp.com
 *
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 *
 * @version 3.1.0
 */
interface ResolverInterface
{
    /**
     * Solve and/or returns an MiddlewareInterface.
     *
     * @param array|MiddlewareInterface|string $middleware Middleware
     *
     * @return MiddlewareInterface
     */
    public function solve($middleware): MiddlewareInterface;
}
