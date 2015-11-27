<?php
namespace DMS\TornadoHttp;

use Interop\Container\ContainerInterface;

/**
 * Trait general para Middlewares
 *
 * @package TORNADO-HTTP-PHP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornado-php.com
 * @license http://tornado-php.com/licencia/ MIT License
 * @version 1.2.0
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
     * @param ContainerInterface $pContainer Contenedor de dependencias
     */
    public function setContainer(ContainerInterface $pContainer)
    {
        $this->container = $pContainer;
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