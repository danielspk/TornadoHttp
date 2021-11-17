<?php

declare(strict_types=1);

namespace DMS\TornadoHttp;

use DMS\TornadoHttp\Exception\MiddlewareException;
use DMS\TornadoHttp\Resolver\Resolver;
use DMS\TornadoHttp\Resolver\ResolverInterface;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SplQueue;
use function in_array;

/**
 * Main class.
 */
final class TornadoHttp implements RequestHandlerInterface
{
    /**
     * @var SplQueue<mixed> Middleware queue
     */
    private SplQueue $middlewares;

    /**
     * @var mixed Handler Context
     */
    private mixed $context;

    /**
     * Constructor.
     *
     * @param array<array>            $middlewares Middlewares
     * @param null|ResponseInterface  $response    Current Response
     * @param null|ContainerInterface $container   Service Container
     * @param null|ResolverInterface  $resolver    Middleware Resolver
     * @param string                  $environment Environment
     */
    public function __construct(
        array $middlewares = [],
        private ?ResponseInterface $response = null,
        private ?ContainerInterface $container = null,
        private ?ResolverInterface $resolver = null,
        private string $environment = 'dev'
    ) {
        $this->middlewares = new SplQueue();

        $this->addList($middlewares);
    }

    /**
     * Handle.
     *
     * @param ServerRequestInterface $request Request
     *
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!$this->middlewares->isEmpty()) {
            $mdw = $this->middlewares->dequeue();

            if (
                (isset($mdw['methods']) && !in_array($request->getMethod(), $mdw['methods'], true))
                || (isset($mdw['path']) && 1 !== preg_match($mdw['path'], $request->getUri()->getPath()))
                || (isset($mdw['env']) && !in_array($this->environment, $mdw['env'], true))
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
     * Register one middleware.
     *
     * @param mixed              $middleware   Middleware
     * @param null|string        $path         Path
     * @param null|array<string> $methods      Methods allowed
     * @param null|array<string> $environments Environment allowed
     * @param null|int           $index        Index of the queue
     */
    public function add(
        mixed $middleware,
        ?string $path = null,
        ?array $methods = null,
        ?array $environments = null,
        ?int $index = null
    ): void {
        $mdw = [
            'middleware' => $middleware,
            'path' => $path,
            'methods' => $methods,
            'env' => $environments,
        ];

        if (null !== $index && $this->middlewares->offsetExists($index)) {
            $this->middlewares->add($index, $mdw);

            return;
        }

        $this->middlewares->enqueue($mdw);
    }

    /**
     * Register middleware from an array.
     *
     * @param array<array> $middlewares Middlewares
     */
    public function addList(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares->enqueue($middleware);
        }
    }

    /**
     * Return the current index of the middlewares queue.
     */
    public function getMiddlewareIndex(): int
    {
        return $this->middlewares->key();
    }

    /**
     * Set the Service Container.
     *
     * @param ContainerInterface $container Service Container
     *
     * @return TornadoHttp
     */
    public function setDI(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the Service Container.
     */
    public function getDI(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * Set the default Response.
     *
     * @param ResponseInterface $response Response
     *
     * @return TornadoHttp
     */
    public function setResponse(ResponseInterface $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the last Response.
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }

    /**
     * Set the Context.
     *
     * @param mixed $context Context
     *
     * @return TornadoHttp
     */
    public function setContext(mixed $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the Context.
     */
    public function getContext(): mixed
    {
        return $this->context;
    }

    /**
     * Set the Middleware Resolver.
     *
     * @param ResolverInterface $resolver Middleware Resolver
     *
     * @return TornadoHttp
     */
    public function setResolver(ResolverInterface $resolver): self
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * Set the Environment execution.
     *
     * @param string $environment Environment
     *
     * @return TornadoHttp
     */
    public function setEnvironment(string $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * Solve and/or returns an MiddlewareInterface.
     *
     * @param mixed $middleware Middleware
     *
     * @throws MiddlewareException
     */
    public function resolveMiddleware(mixed $middleware): MiddlewareInterface
    {
        if (!$this->resolver) {
            $this->resolver = new Resolver($this->container);
        }

        return $this->resolver->solve($middleware);
    }
}
