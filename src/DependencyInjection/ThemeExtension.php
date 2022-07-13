<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\DependencyInjection;

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
        $resourcesDir = dirname(__FILE__, 2) . '/Resources/config';
        $loader = new PhpFileLoader($container, new FileLocator($resourcesDir));
        $loader->load('services.php');

        if (!empty($configs['test'])) {
            $loader->load('test.php');
        }
    }
}
