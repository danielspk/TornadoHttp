<?php
namespace DMS\TornadoHttp;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Clase principal contenedora de la aplicación y los middlewares
 *
 * @package TORNADO-HTTP-PHP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornado-php.com
 * @license http://tornado-php.com/licencia/ MIT License
 * @version 0.1.0
 */
final class TornadoHttp {

    /**
     * @var \SplQueue
     */
    private $queue;

    /**
     * Constructor del contenedor de aplicación
     *
     * @param array $pMiddlewares Middlewares
     */
    public function __construct(array $pMiddlewares = [])
    {
        $this->queue = new \SplQueue();

        foreach ($pMiddlewares as $middleware) {
            $this->queue->enqueue($middleware);
        }
    }

    /**
     * Registro de nuevo middleware
     *
     * @param callable $pMiddleware
     */
    public function add(callable $pMiddleware)
    {
        $this->queue->enqueue($pMiddleware);
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
        if (!$this->queue->isEmpty()) {
            $middleware = $this->queue->dequeue();
        } else {
            $middleware = function(RequestInterface $pRequest, ResponseInterface $pResponse, callable $pNext) {
                return $pResponse;
            };
        }

        if (is_string($middleware)) {
            $middleware = new $middleware;
        } else if (is_array($middleware)) {
            $class = new \ReflectionClass($middleware[0]);
            $middleware = $class->newInstanceArgs($middleware[1]);
        }

        return $middleware($pRequest, $pResponse, $this);
    }

    /**
     * Creación dinámica de atributo
     *
     * @param $pName Nombre de atributo
     * @param $pValue Valor de atributo
     */
    public function createAttribute($pName, $pValue) {
        $this->$pName = $pValue;
    }

}
