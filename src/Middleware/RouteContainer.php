<?php
namespace DMS\TornadoHttp\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use FastRoute;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

class RouteContainer {

    /**
     * @var array Archivos de rutas
     */
    private $files;

    /**
     * Constructor
     *
     * @param array $pFiles Archivos de rutas
     */
    public function __construct(array $pFiles)
    {
        $this->files = $pFiles;
    }

    /**
     * Invocación de registración de contenedor de rutas
     *
     * @param RequestInterface $pRequest Petición
     * @param ResponseInterface $pResponse Respuesta
     * @param callable $pNext Próximo Middleware
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $pRequest, ResponseInterface $pResponse, callable $pNext)
    {

        $dispatcher = FastRoute\simpleDispatcher(function(RouteCollector $r) {

            foreach ($this->files as $file) {

                if (file_exists($file)) {
                    require $file;
                }

            }

        });

        $route = $dispatcher->dispatch($pRequest->getMethod(), $pRequest->getUri()->getPath());

        switch ($route[0]) {
            case Dispatcher::NOT_FOUND:
                return $pResponse->withStatus(404); //crear error
            case Dispatcher::METHOD_NOT_ALLOWED:
                return $pResponse->withStatus(405); // crear error
            case Dispatcher::FOUND:
                $handler = $route[1];
                $vars = $route[2];
                break;
        }

        foreach ($vars as $name => $value) {
            $pRequest = $pRequest->withAttribute($name, $value);
        }

        $pResponse = $this->executeRoute($handler, $pRequest, $pResponse);

        return $pNext($pRequest, $pResponse);
    }

    /**
     * SIN COMENTAR - EN CONSTRUCCION
     *
     * @param $pHandler
     * @param $pRequest
     * @param $pResponse
     * @return mixed
     */
    public function executeRoute($pHandler, $pRequest, $pResponse)
    {
        return $pResponse;
    }

}