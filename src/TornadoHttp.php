<?php
namespace DMS\TornadoHttp;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Main class
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 1.3.6
 */
final class TornadoHttp
{
    /**
     * @var \SplQueue Middleware queue
     */
    private $middlewares;

    /**
     * @var ContainerInterface Service Container
     */
    private $container;

    /**
     * Constructor
     *
     * @param array $middlewares Middlewares
     * @param ContainerInterface $container Service Container
     */
    public function __construct(
        array $middlewares = [],
        ContainerInterface $container = null
    )
    {
        $this->middlewares = new \SplQueue();
        $this->container = $container;

        $this->addList($middlewares);
    }

    /**
     * Invocation
     *
     * @param RequestInterface $request Request
     * @param ResponseInterface $response Response
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        if (!$this->middlewares->isEmpty()) {
            
            $mw = $this->middlewares->dequeue();
            
            if (
                (isset($mw['methods']) && !in_array($request->getMethod(), $mw['methods'])) ||
                (isset($mw['path']) && preg_match($mw['path'], $request->getUri()->getPath()) !== 1)
            ) {
                $next = $this->emptyNext();
            } else {
                $next = $this->resolveCallable($mw['middleware']);
            }
            
        } else {
            $next = $this->finishNext();
        }

        return $next($request, $response, $this);
    }

    /**
     * Register one middleware
     *
     * @param callable|object|string|array $middleware Middleware
     * @param string $path Path
     * @param array $methods Methods allowed
     * @param integer $index Index of the queue
     */
    public function add($middleware, $path = null, $methods = null, $index = null)
    {
        $mw = [
            'middleware' => $middleware,
            'path'       => $path,
            'methods'    => $methods
        ];
        
        if ($index && $this->middlewares->offsetExists($index)) {
            $this->middlewares->add($index, $mw);
        } else {
            $this->middlewares->enqueue($mw);
        }
    }

    /**
     * Register middleware from an array
     *
     * @param array $middlewares Middlewares
     */
    public function addList(array $middlewares)
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares->enqueue($middleware);
        }
    }
    
    /**
     * Return the current index of the middlewares queue
     *
     * @return integer
     */
    public function getMiddlewareIndex()
    {
        return $this->middlewares->key();
    }
    
    /**
     * Set the Service Container
     *
     * @param ContainerInterface $container Service Container
     */
    public function setDI(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Get the Service Container
     *
     * @return ContainerInterface Service Container
     */
    public function getDI()
    {
        return $this->container;
    }

    /**
     * Solve and/or returns an callable or instance class
     *
     * @param callable|string|array $callable Callable
     * @return callable|object Callable or instance class
     */
    public function resolveCallable($callable)
    {
        $middleware = $callable;

        if (is_string($callable)) {
            if (substr($callable, 0, 1) === '@') {
                $middleware = $this->container->get(substr($callable, 1));
            } else{
                $middleware = new $callable;
            }
        } else if (is_array($callable)) {
            $class = new \ReflectionClass($callable[0]);
            $middleware = $class->newInstanceArgs($callable[1]);
        }

        $this->setContainerInTrait($middleware);

        return $middleware;
    }

    /**
     * Return an empty next callable
     *
     * @return callable Next callable
     */
    private function emptyNext()
    {
        return function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $next($request, $response);
        };
    }
    
    /**
     * Return an finish next callable
     *
     * @return callable Next callable
     */
    private function finishNext()
    {
        return function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };
    }
    
    /**
     * Resolved if in the object hierarchy used the ContainerTrait and injects the Container
     *
     * @param object $middleware Middleware object
     */
    private function setContainerInTrait($middleware)
    {
        $rc = new \ReflectionClass($middleware);

        $recursiveTraits = function (\ReflectionClass $class) use(&$recursiveTraits, &$middleware) {

            if (in_array('DMS\TornadoHttp\ContainerTrait', $class->getTraitNames())) {
                $middleware->setContainer($this->container);
                return;
            }

            if ($class->getParentClass() !== false) {
                $recursiveTraits($class->getParentClass());
            }

        };

        $recursiveTraits($rc);
    }
}
