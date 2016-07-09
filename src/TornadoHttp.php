<?php
namespace DMS\TornadoHttp;

use DMS\TornadoHttp\Exception\MiddlewareException;
use DMS\TornadoHttp\Resolver\Resolver;
use DMS\TornadoHttp\Resolver\ResolverInterface;
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
 * @version 1.4.0
 */
final class TornadoHttp
{
    /**
     * Version
     */
    const VERSION = '1.4.0';

    /**
     * @var \SplQueue Middleware queue
     */
    private $middlewares;

    /**
     * @var ContainerInterface Service Container
     */
    private $container;

    /**
     * @var ResolverInterface Middleware Resolver
     */
    private $resolver;

    /**
     * @var string Environment
     */
    private $environment;

    /**
     * Constructor
     *
     * @param array $middlewares Middlewares
     * @param ContainerInterface $container Service Container
     * @param ResolverInterface $resolver Middleware Resolver
     * @param string $environment Environment
     */
    public function __construct(
        array $middlewares = [],
        ContainerInterface $container = null,
        ResolverInterface $resolver = null,
        $environment = 'dev'
    )
    {
        $this->middlewares = new \SplQueue();
        $this->container = $container;
        $this->resolver = $resolver;
        $this->environment = $environment;

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
            
            $mdw = $this->middlewares->dequeue();
            
            if (
                (isset($mdw['methods']) && !in_array($request->getMethod(), $mdw['methods'])) ||
                (isset($mdw['path']) && preg_match($mdw['path'], $request->getUri()->getPath()) !== 1) ||
                (isset($mdw['env']) && !in_array($this->environment, $mdw['env']))
            ) {
                $next = $this->emptyNext();
            } else {
                $next = $this->resolveMiddleware($mdw['middleware']);
            }
            
        } else {
            $next = $this->finishNext();
        }

        return $next($request, $response, $this);
    }

    /**
     * Register one middleware
     *
     * @todo: validate types params
     * @param callable|object|string|array $middleware Middleware
     * @param string $path Path
     * @param array $methods Methods allowed
     * @param array $environments Environment alowed
     * @param integer $index Index of the queue
     */
    public function add($middleware, $path = null, $methods = null, $environments = null, $index = null)
    {
        $mdw = [
            'middleware' => $middleware,
            'path'       => $path,
            'methods'    => $methods,
            'env'        => $environments
        ];
        
        if ($index !== null && $this->middlewares->offsetExists($index)) {
            $this->middlewares->add($index, $mdw);
        } else {
            $this->middlewares->enqueue($mdw);
        }
    }

    /**
     * Register middleware from an array
     *
     * @todo: validate array format
     * @param array $middlewares Middlewares
     * @throws MiddlewareException
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
     * Set the Middleware Resolver
     *
     * @param ResolverInterface $resolver Middleware Resolver
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Set the Environment execution
     *
     * @param string $environment Environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * Solve and/or returns an callable or instance class
     *
     * @param callable|string|array $middleware Middleware
     * @return callable|object Callable or instance class
     */
    public function resolveMiddleware($middleware)
    {
        if (!$this->resolver) {
            $this->resolver = new Resolver($this->container);
        }

        return $this->resolver->solve($middleware);
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
}
