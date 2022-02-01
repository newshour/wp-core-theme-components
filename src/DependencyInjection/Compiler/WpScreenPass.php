<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\DependencyInjection\Compiler;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;
use NewsHour\WPCoreThemeComponents\Admin\Screens\ScreenInterface;

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

            $screenId = $this->getScreenId($reflector, $definition);

            if (empty($screenId)) {
                throw new InvalidArgumentException(
                    sprintf(
                        //phpcs:ignore
                        'Service "%s" must define a valid screen identifier, either in the service configuration as an argument (`$screenId`) or as a class constant (`%s::SCREEN_ID`).',
                        $id,
                        ScreenInterface::class,
                        ScreenInterface::class
                    )
                );
            }

            $underscored = preg_replace('/[\s\-]+/', '_', $screenId);
            $container->setAlias('wp.screen.' . $underscored, $id)->setPublic(true);
            $screens[] = $screenId;
        }

        $container->setParameter('registered_wp_screen_ids', $screens);
    }

    /**
     * Determine the mapped screen ID.
     *
     * @param ReflectionClass $reflector
     * @param Definition $definition
     * @return string
     */
    private function getScreenId(ReflectionClass $reflector, Definition $definition): string
    {
        if ($reflector->hasConstant('SCREEN_ID')) {
            return $reflector->getConstant('SCREEN_ID');
        }

        try {
            return $definition->getArgument('$screenId');
        } catch (OutOfBoundsException $obe) {
            // pass
        }

        return '';
    }
}
