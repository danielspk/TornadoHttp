<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Resolver;

use DMS\TornadoHttp\Container\InjectContainerInterface;
use DMS\TornadoHttp\Exception\MiddlewareException;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use ReflectionException;
use function in_array;
use function is_string;

/**
 * Middleware Resolver class.
 */
class Resolver implements ResolverInterface
{
    /**
     * Constructor.
     *
     * @param null|ContainerInterface $container Service Container
     */
    public function __construct(
        private ?ContainerInterface $container = null
    ) {
    }

    /**
     * Solve and/or returns an MiddlewareInterface.
     *
     * @param array<mixed|string>|MiddlewareInterface|string $middleware Middleware
     *
     * @throws MiddlewareException
     */
    public function solve(mixed $middleware): MiddlewareInterface
    {
        $middleware = $this->makeMiddleware($middleware);

        if (!$middleware instanceof MiddlewareInterface) {
            throw new MiddlewareException('Middleware is not a PSR 15 Middleware Interface');
        }

        if ($this->container && $this->requireContainer($middleware)) {
            $middleware->setContainer($this->container);
        }

        return $middleware;
    }

    /**
     * Make middleware object.
     *
     * @param array<mixed|string>|MiddlewareInterface|string $middleware Middleware
     */
    private function makeMiddleware(array | string | MiddlewareInterface $middleware): ?object
    {
        if (is_string($middleware)) {
            if ($this->container && $this->container->has($middleware)) {
                return $this->container->get($middleware);
            }

            $middleware = [$middleware, []];
        }

        if (is_array($middleware)) {
            try {
                $class = new ReflectionClass($middleware[0]);

                return $class->newInstanceArgs($middleware[1]);
            } catch (ReflectionException) {
                return null;
            }
        }

        return $middleware;
    }

    /**
     * Check if the middleware implements ContainerTrait or InjectContainerInterface.
     *
     * @param MiddlewareInterface $middleware Middleware
     */
    private function requireContainer(MiddlewareInterface $middleware): bool
    {
        $class = new ReflectionClass($middleware);

        $recursiveReflection = static function (ReflectionClass $class) use (&$recursiveReflection) {
            if (
                in_array('DMS\TornadoHttp\Container\ContainerTrait', $class->getTraitNames(), true)
                || in_array(InjectContainerInterface::class, $class->getInterfaceNames(), true)
            ) {
                return true;
            }

            if (false !== $class->getParentClass()) {
                return $recursiveReflection($class->getParentClass());
            }

            return false;
        };

        return $recursiveReflection($class);
    }
}
