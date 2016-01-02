<?php
namespace DMS\TornadoHttp;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Clase principal contenedora de la aplicación
 *
 * @package TORNADO-HTTP-PHP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornado-php.com
 * @license http://tornado-php.com/licencia/ MIT License
 * @version 1.3.0
 */
final class TornadoHttp
{
    /**
     * @var \SplQueue Cola de middlewares
     */
    private $middlewares;

    /**
     * @var ContainerInterface Contenedor de dependencias
     */
    private $container;

    /**
     * Constructor del contenedor de aplicación
     *
     * @param array $middlewares Middlewares
     * @param ContainerInterface $container Contenedor de dependencias
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
     * Invoca la petición/respuesta de inicio de aplicación
     *
     * @param RequestInterface $request Peticion
     * @param ResponseInterface $response Respuesta
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
     * Registra un middleware
     *
     * @param callable|string|array $middleware Middleware
     * @param string $path Path
     * @param array $methods Métodos permitidos
     * @param integer $index Índice de la cola
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
     * Registra middlewares a partir de un array
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
     * Retorna el índice actual de la cola de middlewares
     *
     * @return integer
     */
    public function getMiddlewareIndex()
    {
        return $this->middlewares->key();
    }
    
    /**
     * Asigna el contenedor de dependencias
     *
     * @param ContainerInterface $container Contenedor de dependencias
     */
    public function setDI(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Retorna el contenedor de dependencias
     *
     * @return ContainerInterface Contenedor de dependencias
     */
    public function getDI()
    {
        return $this->container;
    }

    /**
     * Resuelve y/o retorna un callable o instancia de clase
     *
     * @param callable|string|array $callable Solicitud a resolver
     * @return callable Callable o instancia de clase
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
     * Retorna un next vacio
     *
     * @return callable Next
     */
    private function emptyNext()
    {
        return function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $next($request, $response);
        };
    }
    
    /**
     * Retorna un next de finalización
     *
     * @return callable Next
     */
    private function finishNext()
    {
        return function(RequestInterface $request, ResponseInterface $response, callable $next) {
            return $response;
        };
    }
    
    /**
     * Resuelve si en la jerarquía de objetos se usa el ContainerTrait e inyecta el Contenedor
     *
     * @param object $middleware Objeto middleware
     */
    private function setContainerInTrait($middleware)
    {
        $rc = new \ReflectionClass($middleware);

        $recursiveTraits = function ($class) use(&$recursiveTraits, &$middleware) {

            if (in_array('DMS\TornadoHttp\ContainerTrait', $class->getTraitNames())) {
                $middleware->setContainer($this->container);
                return;
            }

            if ($class->getParentClass() != false) {
                $recursiveTraits($class->getParentClass());
            }

        };

        $recursiveTraits($rc);
    }
}
