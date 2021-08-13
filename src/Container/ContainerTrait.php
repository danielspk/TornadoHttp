<?php

declare(strict_types=1);

namespace DMS\TornadoHttp\Container;

use Psr\Container\ContainerInterface;

/**
 * Trait to register Service Container.
 */
trait ContainerTrait
{
    /**
     * @var ContainerInterface Service Container
     */
    protected ContainerInterface $container;

    /**
     * Set the Service Container.
     *
     * @param ContainerInterface $container Service Container
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the Service Container.
     *
     * @return ContainerInterface Service Container
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}
