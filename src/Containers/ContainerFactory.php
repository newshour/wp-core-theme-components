<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Containers;

use Exception;
use InvalidArgumentException;
use Composer\Script\Event;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * Retrieves the container object.
 */
final class ContainerFactory
{
    public const CACHE_NAMESPACE = 'container';

    /**
     * @var Container
     */
    private static $instance;

    /**
     * @return Container
     */
    public static function get(): Container
    {
        if (!empty(self::$instance)) {
            return self::$instance;
        }

        try {
            $containerBuilder = new ContainerBuilder();
            $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__));
            $loader->load('Configuration.php');

            // Apply any container filters.
            $containerBuilder = apply_filters('core_theme_container', $containerBuilder);

            // ...now compile the container.
            $containerBuilder->compile();

            self::$instance = $containerBuilder;
        } catch (InvalidArgumentException $iae) {
            trigger_error($iae->getMessage(), E_USER_ERROR);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }

        return self::$instance;
    }

    /**
     * Clears the cached container.
     *
     * @param Event $event
     * @return void
     */
    public static function dumpAutoload(Event $event): void
    {
        try {
            $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
            $cacheDir = rtrim($vendorDir, '/') . '/newshour/cache/';
            $cacheAdapter = new PhpFilesAdapter(self::CACHE_NAMESPACE, 0, $cacheDir);
            $cacheAdapter->delete('container');
        } catch (InvalidArgumentException $iae) {
            trigger_error($iae->getMessage(), E_USER_ERROR);
        } catch (CacheException $ce) {
            trigger_error($ce->getMessage(), E_USER_ERROR);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
