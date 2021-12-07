<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Containers;

use Exception;
use InvalidArgumentException;
use Composer\Script\Event;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Controller\ErrorController;

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
            if (!defined('BASE_DIR')) {
                trigger_error('The constant BASE_DIR (project root) must be defined.');
            }

            $cacheDir = \trailingslashit(BASE_DIR) . 'cache';

            if (!is_dir($cacheDir)) {
                mkdir($cacheDir, 0755);
            }

            if (!is_writable($cacheDir)) {
                trigger_error($cacheDir . ' dir is not writable.');
            }

            $cacheFile = $cacheDir . '/container.php';
            $containerConfigCache = new ConfigCache($cacheFile, WP_DEBUG);

            if (!$containerConfigCache->isFresh()) {
                // Setup the container.
                $containerBuilder = new ContainerBuilder();

                // Add the framework bundle.
                $frameworkExt = new FrameworkExtension();
                $containerBuilder->registerExtension($frameworkExt);
                $containerBuilder->loadFromExtension($frameworkExt->getAlias(), [
                    'translator' => ['enabled' => false]
                ]);

                // Load any custom configs for the theme.
                $themeExt = new DependencyInjection\ThemeExtension();
                $containerBuilder->registerExtension($themeExt);
                $containerBuilder->loadFromExtension($themeExt->getAlias());

                // Load Symfony package configs.
                $packages = new YamlFileLoader(
                    $containerBuilder,
                    new FileLocator(trailingslashit(BASE_DIR) . 'config/packages')
                );
                $packages->import('*', 'yaml');

                $containerBuilder->setParameter('kernel.debug', WP_DEBUG);
                $containerBuilder->setParameter('kernel.charset', 'utf-8');
                $containerBuilder->setParameter('kernel.project_dir', BASE_DIR);
                $containerBuilder->setParameter('kernel.cache_dir', \trailingslashit(BASE_DIR) . 'cache');
                $containerBuilder->setParameter('kernel.build_dir', \trailingslashit(BASE_DIR) . 'cache/build');
                $containerBuilder->setParameter('kernel.container_class', ContainerBuilder::class);
                $containerBuilder->setParameter('kernel.error_controller', ErrorController::class);
                $containerBuilder->setParameter('kernel.bundles_metadata', []);
                $containerBuilder->setParameter('kernel.runtime_environment', WP_ENV);
                $containerBuilder->setParameter('kernel.default_locale', 'en_US');

                // Apply any container filters.
                $containerBuilder = apply_filters('core_theme_container', $containerBuilder);

                // ...now compile the container.
                $containerBuilder->compile();

                $dumper = new PhpDumper($containerBuilder);
                $containerConfigCache->write(
                    $dumper->dump(['class' => 'CoreThemeCachedContainer']),
                    $containerBuilder->getResources()
                );
            }

            require_once $cacheFile;
            $containerClass = '\\CoreThemeCachedContainer';
            self::$instance = new $containerClass();
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
            $cachedContainer = dirname($vendorDir) . '/cache/container.php';

            if (file_exists($cachedContainer)) {
                unlink($cachedContainer);
            }
        } catch (InvalidArgumentException $iae) {
            trigger_error($iae->getMessage(), E_USER_ERROR);
        } catch (CacheException $ce) {
            trigger_error($ce->getMessage(), E_USER_ERROR);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_ERROR);
        }
    }
}
