<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\DependencyInjection\Compiler;

use InvalidArgumentException;
use NewsHour\WPCoreThemeComponents\Admin\Screens\ScreenInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WpScreenPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     * @throws InvalidArgumentException
     * @return void
     */
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('wp.screen');

        if (count($taggedServices) < 1) {
            return;
        }

        $screens = [];

        foreach ($taggedServices as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();

            if (!($reflector = $container->getReflectionClass($class))) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Class "%s" used for service "%s" cannot be found.',
                        $class,
                        $id
                    )
                );
            }

            if (!$reflector->implementsInterface(ScreenInterface::class)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Service "%s" must implement interface "%s".',
                        $id,
                        ScreenInterface::class
                    )
                );
            }

            if (!$reflector->hasConstant('SCREEN_ID')) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Service "%s" must set class constant: `SCREEN_ID`',
                        $id,
                        ScreenInterface::class
                    )
                );
            }

            $screenId = $reflector->getConstant('SCREEN_ID');
            $underscored = preg_replace('/[\s\-]+/', '_', $screenId);
            $container->setAlias('wp.screen.' . $underscored, $id)->setPublic(true);
            $screens[] = $screenId;
        }

        $container->setParameter('registered_wp_screen_ids', $screens);
    }
}
