<?php
namespace DMS\TornadoHttp\Middleware;

use DMS\TornadoHttp\Container\InjectContainerInterface;
use Interop\Container\ContainerInterface;

/**
 * Middleware abstract class
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 1.5.0
 */
abstract class Middleware implements MiddlewareInterface, InjectContainerInterface
{
    /**
     * @var ContainerInterface Service Container
     */
    protected $container;

    /**
     * Set the Service Container
     *
     * @param ContainerInterface $container Service Container
     * @return Middleware
     */
    public function setContainer(ContainerInterface $container) : Middleware
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the Service Container
     *
     * @return ContainerInterface Service Container
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }
}