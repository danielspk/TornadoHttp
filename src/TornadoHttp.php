<?php
namespace DMS\TornadoHttp;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Clase principal contenedora de la aplicación
 *
 * @package TORNADO-HTTP-PHP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornado-php.com
 * @license http://tornado-php.com/licencia/ MIT License
 * @version 1.0.0
 */
final class TornadoHttp {

    /**
     * @var \SplQueue Cola de middlewares
     */
    private $middlewares;

    /**
     * @var \ArrayAccess Contenedor de dependencias
     */
    private $containerDI;

    /**
     * Constructor del contenedor de aplicación
     *
     * @param array $pMiddlewares Middlewares
     * @param \ArrayAccess $pContainer Contenedor de dependencias
     */
    public function __construct(
        array $pMiddlewares = [],
        \ArrayAccess $pContainer = null
    )
    {
        $this->middlewares      = new \SplQueue();
        $this->containerDI      = $pContainer;

        $this->registerMiddlewareArray($pMiddlewares);
    }

    /**
     * Invoca la petición/respuesta de inicio de aplicación
     *
     * @param RequestInterface $pRequest Peticion
     * @param ResponseInterface $pResponse Respuesta
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $pRequest, ResponseInterface $pResponse)
    {
        if (!$this->middlewares->isEmpty()) {
            $next = $this->resolveCallable($this->middlewares->dequeue());
        } else {
            $next = function(RequestInterface $pRequest, ResponseInterface $pResponse, callable $pNext) {
                return $pResponse;
            };
        }

        return $next($pRequest, $pResponse, $this);
    }

    /**
     * Registra middlewares
     *
     * @param callable|string|array $pMiddleware
     */
    public function add($pMiddleware)
    {
        if (is_array($pMiddleware)) {
            $this->registerMiddlewareArray($pMiddleware);
        } else {
            $this->middlewares->enqueue($pMiddleware);
        }
    }

    /**
     * Retorna la cola de middlewares
     *
     * @return \SplQueue
     */
    public function getMiddlewares()
    {
        return $this->middlewares;
    }

    /**
     * Asigna el contenedor de dependencias
     *
     * @param \ArrayAccess $pContainer Contenedor de dependencias
     */
    public function setDI(\ArrayAccess $pContainer)
    {
        $this->containerDI = $pContainer;
    }

    /**
     * Retorna el contenedor de dependencias
     *
     * @return \ArrayAccess Contenedor de dependencias
     */
    public function getDI()
    {
        return $this->containerDI;
    }

    /**
     * Resuelve y/o retorna un callable o instancia de clase
     *
     * @param callable|string|array $pCallable Solicitud a resolver
     * @return callable Callable o instancia de clase
     */
    public function resolveCallable($pCallable)
    {
        $callable = $pCallable;

        if (is_string($pCallable)) {
            $callable = new $pCallable;
        } else if (is_array($pCallable)) {
            $class = new \ReflectionClass($pCallable[0]);
            $callable = $class->newInstanceArgs($pCallable[1]);
        }

        return $callable;
    }

    /**
     * Registra middlewares a partir de un array
     *
     * @param array $pMiddlewares Middlewares
     */
    private function registerMiddlewareArray(array $pMiddlewares)
    {
        foreach ($pMiddlewares as $middleware) {
            $this->middlewares->enqueue($middleware);
        }
    }

}
