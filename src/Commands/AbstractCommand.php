<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Commands;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Parent Command class.
 *
 * @abstract
 */
abstract class AbstractCommand implements Command, ContainerAwareInterface
{
    private ?ContainerInterface $container = null;

    /**
     * @return string
     */
    abstract public function __toString();

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface|null $container
     * @return void
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
