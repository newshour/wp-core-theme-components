<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ManagerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('core_theme.manager');

        if (count($taggedServices) > 0) {
            foreach ($taggedServices as $id => $tags) {
                $definition = $container->getDefinition($id);
                $definition->setPublic(true);
            }
        }
    }
}
