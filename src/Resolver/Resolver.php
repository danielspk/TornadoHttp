<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Resolver;

use DMS\TornadoHttp\Exception\MiddlewareException;
use Interop\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use ReflectionException;

/**
 * Middleware Resolver class.
 *
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 *
 * @see http://tornadohttp.com
 *
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 *
 * @version 3.1.1
 */
class Resolver implements ResolverInterface
{
    /**
     * @var null|ContainerInterface Service Container
     */
    private $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Solve and/or returns an MiddlewareInterface.
     *
     * @param array|MiddlewareInterface|string $middleware Middleware
     *
     * @throws ReflectionException
     * @throws MiddlewareException
     *
     * @return MiddlewareInterface
     */
    public function solve($middleware): MiddlewareInterface
    {
        if (\is_string($middleware)) {
            if ($this->container && $this->container->has($middleware)) {
                $middleware = $this->container->get($middleware);
            } else {
                $middleware = new $middleware();
            }
        } elseif (\is_array($middleware)) {
            $class = new ReflectionClass($middleware[0]);
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
     * Check if the middleware implements ContainerTrait or InjectContainerInterface.
     *
     * @param MiddlewareInterface $middleware Middleware
     *
     * @throws ReflectionException
     *
     * @return bool
     */
    private function requireContainer(MiddlewareInterface $middleware): bool
    {
        $rc = new \ReflectionClass($middleware);

        $recursiveReflection = function (ReflectionClass $class) use (&$recursiveReflection) {
            if (
                \in_array('DMS\TornadoHttp\Container\ContainerTrait', $class->getTraitNames(), true) ||
                \in_array('DMS\TornadoHttp\Container\InjectContainerInterface', $class->getInterfaceNames(), true)
            ) {
                return true;
            }

            if (false !== $class->getParentClass()) {
                return $recursiveReflection($class->getParentClass());
            }

            return false;
        };

        return $recursiveReflection($rc);
    }
}
