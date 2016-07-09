<?php
namespace DMS\TornadoHttp\Resolver;

/**
 * Middleware Resolver interface
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 1.4.0
 */
interface ResolverInterface
{
    /**
     * Solve and/or returns an callable or instance class
     *
     * @param callable|string|array $middleware Middleware
     * @return callable|object Callable or instance class
     */
    public function solve($middleware);
}
