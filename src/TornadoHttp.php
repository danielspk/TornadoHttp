<?php

declare(strict_types = 1);

namespace DMS\TornadoHttp;

use DMS\TornadoHttp\Exception\MiddlewareException;
use DMS\TornadoHttp\Resolver\Resolver;
use DMS\TornadoHttp\Resolver\ResolverInterface;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Main class
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 3.0.0
 */
final class TornadoHttp implements RequestHandlerInterface
{
    /**
     * Version
     */
    public const VERSION = '3.0.0';

    /**
     * @var array Global Attributes
     */
    public $globalAttributes;

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
     * @var ResponseInterface Current Response
     */
    private $response;

    /**
     * Constructor
     *
     * @param array $middlewares Middlewares
     * @param ResponseInterface $response Response
     * @param ContainerInterface $container Service Container
     * @param ResolverInterface $resolver Middleware Resolver
     * @param string $environment Environment
     */
    public function __construct(
        array $middlewares = [],
        ResponseInterface $response = null,
        ContainerInterface $container = null,
        ResolverInterface $resolver = null,
        string $environment = 'dev'
    ) {
        $this->middlewares = new \SplQueue();
        $this->response = $response;
        $this->container = $container;
        $this->resolver = $resolver;
        $this->environment = $environment;

        $this->addList($middlewares);
    }

    /**
     * Handle
     *
     * @param ServerRequestInterface $request Request
     * @throws MiddlewareException
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if (!$this->middlewares->isEmpty()) {
            $mdw = $this->middlewares->dequeue();

            if (
                (isset($mdw['methods']) && !in_array($request->getMethod(), $mdw['methods'])) ||
                (isset($mdw['path']) && preg_match($mdw['path'], $request->getUri()->getPath()) !== 1) ||
                (isset($mdw['env']) && !in_array($this->environment, $mdw['env']))
            ) {
                return $this->handle($request);
            }

            $next = $this->resolveMiddleware($mdw['middleware']);

            $this->response = $next->process($request, $this);

            return $this->response;
        }

        if (!$this->response) {
            throw new MiddlewareException('Empty response');
        }

        return $this->response;
    }

    /**
     * Register one middleware
     *
     * @param mixed $middleware Middleware
     * @param string $path Path
     * @param array $methods Methods allowed
     * @param array $environments Environment allowed
     * @param integer $index Index of the queue
     */
    public function add($middleware, ?string $path = null, ?array $methods = null, ?array $environments = null, ?int $index = null) : void
    {
        $mdw = [
            'middleware' => $middleware,
            'path'       => $path,
            'methods'    => $methods,
            'env'        => $environments,
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
     * @param array $middlewares Middlewares
     */
    public function addList(array $middlewares) : void
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
    public function getMiddlewareIndex() : int
    {
        return $this->middlewares->key();
    }

    /**
     * Set the Service Container
     *
     * @param ContainerInterface $container Service Container
     * @return TornadoHttp
     */
    public function setDI(ContainerInterface $container) : TornadoHttp
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the Service Container
     *
     * @return ContainerInterface|null Service Container
     */
    public function getDI() : ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set the default Response
     *
     * @param ResponseInterface $response Response
     * @return TornadoHttp
     */
    public function setResponse(ResponseInterface $response) : TornadoHttp
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the last Response
     *
     * @return ResponseInterface|null Response
     */
    public function getResponse() : ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the Middleware Resolver
     *
     * @param ResolverInterface $resolver Middleware Resolver
     * @return TornadoHttp
     */
    public function setResolver(ResolverInterface $resolver) : TornadoHttp
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Set the Environment execution
     *
     * @param string $environment Environment
     * @return TornadoHttp
     */
    public function setEnvironment(string $environment) : TornadoHttp
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Solve and/or returns an MiddlewareInterface
     *
     * @param mixed $middleware Middleware
     * @throws MiddlewareException
     * @return MiddlewareInterface Middleware
     */
    public function resolveMiddleware($middleware) : MiddlewareInterface
    {
        if (!$this->resolver) {
            $this->resolver = new Resolver($this->container);
        }

        return $this->resolver->solve($middleware);
    }
}
