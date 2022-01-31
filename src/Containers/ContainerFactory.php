<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Containers;

use Exception;
use InvalidArgumentException;
use Composer\Script\Event;
use NewsHour\WPCoreThemeComponents\CoreThemeKernel;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Retrieves the configured container object. In production, the container
 * is cached and stored at the path defined by kernel.cache_dir.
 * ContainerFactory::dumpAutoload should be set as a composer script.
 *
 * ```
 * "post-autoload-dump": [
 *   "NewsHour\\WPCoreThemeComponents\\Containers\\ContainerFactory::dumpAutoload"
 * ]
 * ```
 *
 * @final
 */
final class ContainerFactory
{
    /**
     * @var ContainerInterface
     */
    private static $instance;

    /**
     * @return ContainerInterface
     */
    public static function get(): ContainerInterface
    {
        if (!empty(self::$instance)) {
            return self::$instance;
        }

        $env = isset($_SERVER['WP_ENV']) ? $_SERVER['WP_ENV'] : '';
        $debug = false;

        if (defined('WP_DEBUG')) {
            $debug = WP_DEBUG;
        } elseif (isset($_SERVER['WP_DEBUG'])) {
            $debug = WP_DEBUG;
        }

        self::$instance = CoreThemeKernel::create($env, $debug)->getContainer();

        return self::$instance;
    }
}
