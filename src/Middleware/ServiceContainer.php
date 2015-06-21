<?php

namespace DMS\TornadoHttp\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Pimple\Container;

class ServiceContainer {

    /**
     * @var array Archivos con servicios
     */
    private $files;

    /**
     * Constructor
     *
     * @param array $pFiles Archivos de servicios
     */
    public function __construct(array $pFiles)
    {
        $this->files = $pFiles;
    }

    /**
     * Invocación de registración de contenedor de servicios
     *
     * @param RequestInterface $pRequest Petición
     * @param ResponseInterface $pResponse Respuesta
     * @param callable $pNext Próximo Middleware
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $pRequest, ResponseInterface $pResponse, callable $pNext)
    {
        $pNext->createAttribute('service', new Container());

        foreach ($this->files as $file) {

            if (file_exists($file)) {
                require $file;
            }

        }

        return $pNext($pRequest, $pResponse);
    }

}