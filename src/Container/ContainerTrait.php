<?php
namespace DMS\TornadoHttp\Container;

use Interop\Container\ContainerInterface;

/**
 * Trait to register Service Container
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 1.4.0
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