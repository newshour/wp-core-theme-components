<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Containers\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class ThemeExtension extends Extension
{
    /**
     * @return string
     */
    public function getAlias(): string
    {
        return 'theme';
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        // Setup any custom configs here...

        // Load required Core Theme services.
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__));
        $loader->load('configuration.php');
    }
}
