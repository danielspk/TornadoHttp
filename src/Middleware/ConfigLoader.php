<?php
namespace DMS\TornadoHttp\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ConfigLoader {

    /**
     * @var array Archivos de configuración
     */
    private $files;

    /**
     * Constructor
     *
     * @param array $pFiles Archivos de configuración
     */
    public function __construct(array $pFiles)
    {
        $this->files = $pFiles;
    }

    /**
     * Invocación de carga de archivos de configuración
     *
     * @param RequestInterface $pRequest Peticion
     * @param ResponseInterface $pResponse Respuesta
     * @param callable $pNext Próximo Middleware
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $pRequest, ResponseInterface $pResponse, callable $pNext)
    {
        $pNext->createAttribute('config', []);

        foreach ($this->files as $file) {

            if (file_exists($file)) {
                $pNext->config = array_merge($pNext->config, require $file);
            }

        }

        return $pNext($pRequest, $pResponse);
    }

}