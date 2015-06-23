<?php
namespace DMS\TornadoHttp\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use DMS\TornadoHttp\Helper\Config;

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
        $config = new Config();

        foreach ($this->files as $file) {

            if (file_exists($file)) {
                $config->set(require $file);
            }

        }

        /** @var \DMS\TornadoHttp\TornadoHttp $pNext */
        $pNext->setConfig($config);

        return $pNext($pRequest, $pResponse);
    }

}