<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Factories;

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class FileLoaderFactory
{
    /**
     * @param string $type
     * @param ContainerBuilder $container
     * @param FileLocatorInterface $locator
     * @return FileLoader|null
     */
    public static function create(string $type, ContainerBuilder $container, FileLocatorInterface $locator): ?FileLoader
    {
        switch ($type) {
            case 'yaml':
                return new YamlFileLoader($container, $locator);

            case 'xml':
                return new XmlFileLoader($container, $locator);

            case 'php':
                return new PhpFileLoader($container, $locator);
        }
    }
}
