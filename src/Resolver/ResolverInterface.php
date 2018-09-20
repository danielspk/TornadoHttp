<?php

namespace DMS\TornadoHttp\Resolver;

use Psr\Http\Server\MiddlewareInterface;

/**
 * Middleware Resolver interface
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 2.1.3
 */
interface ResolverInterface
{
    /**
     * Solve and/or returns an MiddlewareInterface
     *
     * @param MiddlewareInterface|string|array $middleware Middleware
     * @return MiddlewareInterface
     */
    public function solve($middleware) : MiddlewareInterface;
}
