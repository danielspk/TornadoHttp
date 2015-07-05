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
 * @version 0.1.0
 */
final class TornadoHttp {

    /**
     * @var \SplQueue Cola de middlewares
     */
    private $middlewares;

    /**
     * @var object Contenedor de dependencias
     */
    private $containerDI;

    /**
     * @var \ArrayAccess
     */
    private $configuration;

    /**
     * @var callable Handler de excepción personalizada
     */
    private $exceptionHandler;

    /**
     * Constructor del contenedor de aplicación
     *
     * @param array $pMiddlewares Middlewares
     * @param \ArrayAccess $pContainer Contenedor de dependencias
     * @param \ArrayAccess $pConfig Gestor de configuraciones
     * @param callable|string $pHandler Handler de excepción
     */
    public function __construct(
        array $pMiddlewares = [],
        \ArrayAccess $pContainer = null,
        \ArrayAccess $pConfig = null,
        $pHandler = null
    )
    {
        $this->middlewares      = new \SplQueue();
        $this->containerDI      = $pContainer;
        $this->configuration    = $pConfig;
        $this->exceptionHandler = $pHandler;

        $this->registerMiddlewareArray($pMiddlewares);
    }

    /**
     * Invocación de petición/respuesta de inicio de aplicación
     *
     * @param RequestInterface $pRequest Peticion
     * @param ResponseInterface $pResponse Respuesta
     * @return callable
     */
    public function __invoke(RequestInterface $pRequest, ResponseInterface $pResponse)
    {
        if (!$this->middlewares->isEmpty()) {
            $middleware = $this->middlewares->dequeue();
        } else {
            $middleware = function(RequestInterface $pRequest, ResponseInterface $pResponse, callable $pNext) {
                return $pResponse;
            };
        }

        $call = $this->resolveCallable($middleware);

        return $call($pRequest, $pResponse, $this);
    }

    /**
     * Registro de nuevo middleware
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
     * Asignación de contenedor de dependencias
     *
     * @param \ArrayAccess $pContainer Contenedor de dependencias
     */
    public function setDI(\ArrayAccess $pContainer)
    {
        $this->containerDI = $pContainer;
    }

    /**
     * Recupero de contenedor de dependencias
     *
     * @return \ArrayAccess Contenedor de dependencias
     */
    public function getDI()
    {
        return $this->containerDI;
    }

    /**
     * Asignación de configuración de aplicación
     *
     * @param \ArrayAccess $pConfig Gestor de configuraciones
     */
    public function setConfig(\ArrayAccess $pConfig)
    {
        $this->configuration = $pConfig;
    }

    /**
     * Recupero de configuración de aplicación
     *
     * @return \ArrayAccess Configuración de aplicación
     */
    public function getConfig()
    {
        return $this->configuration;
    }

    /**
     * Asignación de handler de excepciones personalizado
     *
     * @param callable|string $pHandler Handler de excepción
     */
    public function setExceptionHandler($pHandler)
    {
        $this->exceptionHandler = $this->resolveCallable($pHandler);
    }

    /**
     * Recupero de handler personalizado de excepciones
     *
     * @return callable
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * Resuelve si debe crear el objeto invocable
     *
     * @param callable|string $pCallable Solicitud callable
     * @return callable
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
     * Registra un array de middlewares en la cola
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
