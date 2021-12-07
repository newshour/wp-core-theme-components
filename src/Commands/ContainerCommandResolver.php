<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Commands;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A resolver for commands which are container aware. Similar to Symfony's
 * ContainerControllerResolver.
 */
class ContainerCommandResolver
{
    protected ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $class
     * @throws InvalidArgumentException
     * @return Command|null
     */
    public function getCommand(string $class): ?Command
    {
        try {
            $command = $this->instantiateCommand($class);
        } catch (\InvalidArgumentException $ive) {
            throw $ive;
        }

        if (!\is_callable($command)) {
            throw new \InvalidArgumentException(
                $class . ' is not callable.'
            );
        }

        return $command;
    }

    /**
     * @param string $class
     * @throws InvalidArgumentException
     * @return Command|null
     */
    private function instantiateCommand(string $class): ?Command
    {
        $class = ltrim($class, '\\');

        if ($this->container->has($class)) {
            return $this->container->get($class);
        }

        try {
            return new $class();
        } catch (\Error $e) {
        }

        throw new \InvalidArgumentException(
            sprintf('Command "%s" neither exists as service nor as class.', $class),
            0,
            $e
        );
    }
}
