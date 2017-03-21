<?php
namespace DMS\TornadoHttp\Resolver;

use DMS\TornadoHttp\Exception\MiddlewareException;
use Interop\Container\ContainerInterface;

/**
 * Middleware Resolver class
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 1.5.0
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
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Solve and/or returns an callable
     *
     * @param callable|object|string|array $middleware Middleware
     * @return callable Callable
     * @throws MiddlewareException
     */
    public function solve($middleware) : callable
    {
        if (is_string($middleware)) {
            if ($this->container && $this->container->has($middleware)) {
                $middleware = $this->container->get($middleware);
            } else{
                $middleware = new $middleware;
            }
        } else if (is_array($middleware)) {
            $class = new \ReflectionClass($middleware[0]);
            $middleware = $class->newInstanceArgs($middleware[1]);
        }

        if (!is_callable($middleware)) {
            throw new MiddlewareException('Middleware is not callable');
        }

        if ($this->container && $this->requireContainer($middleware)) {
            $middleware->setContainer($this->container);
        }

        return $middleware;
    }

    /**
     * Check if the middleware implements ContainerTrait or InjectContainerInterface
     *
     * @param callable $middleware Middleware object
     * @return boolean Use ContainerTrait
     */
    private function requireContainer(callable $middleware) : bool
    {
        /** @var \DMS\TornadoHttp\Container\InjectContainerInterface $middleware */

        $rc = new \ReflectionClass($middleware);

        $recursiveReflection = function (\ReflectionClass $class) use(&$recursiveReflection, &$middleware) {
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
