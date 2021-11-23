<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Http\Factories;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

/**
 * @see https://symfony.com/doc/current/components/asset.html
 */
final class PackageFactory
{
    private static $instance;

    /**
     * @return Package
     */
    public static function get(): Package
    {
        if (self::$instance == null) {
            self::$instance = new Package(self::getVersionStrategy());
        }

        return self::$instance;
    }

    /**
     * @return VersionStrategyInterface
     */
    public static function getVersionStrategy(): VersionStrategyInterface
    {
        $manifestFile = apply_filters('core_theme_default_asset_strategy', new EmptyVersionStrategy());

        if (is_object($manifestFile) && $manifestFile instanceof VersionStrategyInterface) {
            return $manifestFile;
        }

        if (!is_object($manifestFile)) {
            trigger_error(
                // phpcs:ignore
                'The filter "core_theme_asset_strategy" did not return a version strategy object. Defaulting to empty version strategy.',
                E_USER_WARNING
            );
        } else {
            trigger_error(
                // phpcs:ignore
                'The object returned by filter "core_theme_asset_strategy" is not of type VersionStrategyInterface. Defaulting to empty version strategy.',
                E_USER_WARNING
            );
        }

        return new EmptyVersionStrategy();
    }
}
