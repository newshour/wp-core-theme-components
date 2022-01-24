<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Controllers;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;

/**
 * Our container controller resolver. This resolver is essentially the same resolver as
 * the one in Symony's Framework Bundle. The primary difference is that we can pass our
 * container in the constructor and instruct the resolver to check for instances of our
 * parent Controller class.
 *
 * @final
 */
final class ControllerResolver extends ContainerControllerResolver
{
    /**
     * @param ContainerInterface $container
     * @param LoggerInterface|null $logger
     */
    public function __construct(ContainerInterface $container, LoggerInterface $logger = null)
    {
        parent::__construct($container, $logger);
    }

    /**
     * @param string $class
     * @return object
     */
    protected function instantiateController(string $class): object
    {
        $controller = parent::instantiateController($class);

        if ($controller instanceof ContainerAwareInterface) {
            $controller->setContainer($this->container);
            return $controller;
        }

        if ($controller instanceof Controller) {
            $previousContainer = $controller->setContainer($this->container);

            if (empty($previousContainer)) {
                throw new \LogicException(
                    sprintf('"%s" has no container set, did you forget to define it as a service subscriber?', $class)
                );
            }

            $controller->setContainer($previousContainer);
        }

        return $controller;
    }
}
