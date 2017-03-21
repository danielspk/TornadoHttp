<?php
namespace DMS\TornadoHttp\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Middleware interface
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 1.5.0
 */
interface MiddlewareInterface
{
    /**
     * @param RequestInterface $request Request
     * @param ResponseInterface $response Response
     * @param callable $next Next middleware
     * @return ResponseInterface Response
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) : ResponseInterface;
}
