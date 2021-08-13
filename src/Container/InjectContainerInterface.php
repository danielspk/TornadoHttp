<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Container;

use Psr\Container\ContainerInterface;

/**
 * Interface to register Service Container.
 */
interface InjectContainerInterface
{
    /**
     * Set the Service Container.
     *
     * @param ContainerInterface $container Service Container
     */
    public function setContainer(ContainerInterface $container): self;

    /**
     * Get the Service Container.
     *
     * @return ContainerInterface Service Container
     */
    public function getContainer(): ContainerInterface;
}
