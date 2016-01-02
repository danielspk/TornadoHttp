<?php
namespace DMS\TornadoHttp;

use Interop\Container\ContainerInterface;

/**
 * Trait general para registrar el Contenedor de Servicios dentro de Middlewares
 *
 * @package TORNADO-HTTP-PHP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornado-php.com
 * @license http://tornado-php.com/licencia/ MIT License
 * @version 1.3.0
 */
trait ContainerTrait
{
    /**
     * @var ContainerInterface Contenedor de servicios 
     */
    protected $container;
    
    /**
     * Asigna el contenedor de dependencias
     *
     * @param ContainerInterface $container Contenedor de dependencias
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Retorna el contenedor de dependencias
     * 
     * @return ContainerInterface Contenedor de dependencias
     */
    public function getContainer()
    {
        return $this->container;
    }
}