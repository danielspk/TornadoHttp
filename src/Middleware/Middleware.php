<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Middleware;

use DMS\TornadoHttp\Container\InjectContainerInterface;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware abstract class.
 *
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 *
 * @see http://tornadohttp.com
 *
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 *
 * @version 3.1.0
 */
abstract class Middleware implements MiddlewareInterface, InjectContainerInterface
{
    /**
     * @var ContainerInterface Service Container
     */
    protected $container;

    /**
     * Set the Service Container.
     *
     * @param ContainerInterface $container Service Container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the Service Container.
     *
     * @return ContainerInterface Service Container
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Process.
     *
     * @param ServerRequestInterface  $request Request
     * @param RequestHandlerInterface $handler Middleware handlers
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
