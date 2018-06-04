<?php

namespace DMS\TornadoHttp\Container;

use Interop\Container\ContainerInterface;

/**
 * Interface to register Service Container
 *
 * @package TORNADO-HTTP
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 * @link http://tornadohttp.com
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 * @version 2.1.0
 */
interface InjectContainerInterface
{
    /**
     * Set the Service Container
     *
     * @param ContainerInterface $container Service Container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the Service Container
     *
     * @return ContainerInterface Service Container
     */
    public function getContainer() : ContainerInterface;
}
