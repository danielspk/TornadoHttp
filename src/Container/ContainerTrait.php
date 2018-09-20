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
 * @version 2.1.1
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
     * @return ContainerTrait
     */
    public function setContainer(ContainerInterface $container) : self
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
