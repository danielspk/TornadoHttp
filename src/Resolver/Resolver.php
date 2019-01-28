<?php

declare(strict_types = 1);

namespace DMS\TornadoHttp\Resolver;

use DMS\TornadoHttp\Exception\MiddlewareException;
use Interop\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Middleware Resolver class
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 3.0.1
 */
class Resolver implements ResolverInterface
{
    /**
     * @var ContainerInterface Service Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Solve and/or returns an MiddlewareInterface
     *
     * @param MiddlewareInterface|string|array $middleware Middleware
     * @throws \ReflectionException
     * @throws MiddlewareException
     * @return MiddlewareInterface
     */
    public function solve($middleware) : MiddlewareInterface
    {
        if (is_string($middleware)) {
            if ($this->container && $this->container->has($middleware)) {
                $middleware = $this->container->get($middleware);
            } else {
                $middleware = new $middleware;
            }
        } elseif (is_array($middleware)) {
            $class = new \ReflectionClass($middleware[0]);
            $middleware = $class->newInstanceArgs($middleware[1]);
        }

        if (!$middleware instanceof MiddlewareInterface) {
            throw new MiddlewareException('Middleware is not a PSR 15 Middleware Interface');
        }

        if ($this->container && $this->requireContainer($middleware)) {
            $middleware->setContainer($this->container);
        }

        return $middleware;
    }

    /**
     * Check if the middleware implements ContainerTrait or InjectContainerInterface
     *
     * @param MiddlewareInterface $middleware Middleware
     * @throws \ReflectionException
     * @return boolean Use ContainerTrait
     */
    private function requireContainer(MiddlewareInterface $middleware) : bool
    {
        /** @var \DMS\TornadoHttp\Container\InjectContainerInterface $middleware */

        $rc = new \ReflectionClass($middleware);

        $recursiveReflection = function (\ReflectionClass $class) use (&$recursiveReflection, &$middleware) {
            if (
                in_array('DMS\TornadoHttp\Container\ContainerTrait', $class->getTraitNames()) ||
                in_array('DMS\TornadoHttp\Container\InjectContainerInterface', $class->getInterfaceNames())
            ) {
                return true;
            }

            if ($class->getParentClass() !== false) {
                return $recursiveReflection($class->getParentClass());
            }

            return false;
        };

        return $recursiveReflection($rc);
    }
}
