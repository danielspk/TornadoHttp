<?php
namespace DMS\TornadoHttp;

use Interop\Container\ContainerInterface;

/**
 * Trait to register Service Container within Middlewares
 *
 * @package TORNADO-HTTP-PHP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornado-php.com
 * @license http://tornado-php.com/licencia/ MIT License
 * @version 1.3.2
 */
trait ContainerTrait
{
    /**
     * @var ContainerInterface Service Container
     */
    protected $container;
    
    /**
     * Set the Service Container
     *
     * @param ContainerInterface $container Service Container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Get the Service Container
     * 
     * @return ContainerInterface Service Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
