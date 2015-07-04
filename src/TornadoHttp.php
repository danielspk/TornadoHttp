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
 * @version 0.2.1
 */
final class TornadoHttp {

    /**
     * @var \SplQueue Cola de middlewares
     */
    private $middlewares;

    /**
     * @var \ArrayAccess
     */
    private $configuration;

    /**
     * @var object Contenedor de dependencias
     */
    private $containerDI;

    /**
     * @var callable Handler de excepción personalizada
     */
    private $exceptionHandler;

    /**
     * Constructor del contenedor de aplicación
     *
     * @param array $pMiddlewares Middlewares
     */
    public function __construct(array $pMiddlewares = [])
    {
        $this->middlewares      = new \SplQueue();
        $this->configuration    = null;
        $this->containerDI      = null;
        $this->exceptionHandler = null;

        foreach ($pMiddlewares as $middleware) {
            $this->middlewares->enqueue($middleware);
        }
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
     * @param callable|string $pMiddleware
     */
    public function add($pMiddleware)
    {
        $this->middlewares->enqueue($pMiddleware);
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
     * Asignación de configuración de aplicación
     *
     * @param \ArrayAccess $pConfig
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
     * Asignación de contenedor de dependencias
     *
     * @param \ArrayAccess $pContainer
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

}
