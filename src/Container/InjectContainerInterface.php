<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Container;

use Interop\Container\ContainerInterface;

/**
 * Interface to register Service Container.
 *
 * @author Daniel M. Spiridione <info@daniel-spiridione.com.ar>
 *
 * @see http://tornadohttp.com
 *
 * @license https://raw.githubusercontent.com/danielspk/TornadoHttp/master/LICENSE.md MIT License
 *
 * @version 3.1.1
 */
interface InjectContainerInterface
{
    /**
     * Set the Service Container.
     *
     * @param ContainerInterface $container Service Container
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Get the Service Container.
     *
     * @return ContainerInterface Service Container
     */
    public function getContainer(): ContainerInterface;
}
