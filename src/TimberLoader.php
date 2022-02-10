<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents;

use Timber\Timber;

/**
 * Factory class for loading the Timber library. The factory avoids calling
 * `Timber::backwards_compatibility()` which has some incompatibilities with
 * Symfony's autoloader.
 */
final class TimberLoader extends Timber
{
    /** @var Timber */
    private static $instance;

    /**
     * Loads the Timber environment. Initialization values can be optionally
     * passed as a dictionary: 'locations', 'dirname', 'cache', 'autoescape'
     *
     * @param array $init
     * @return Timber
     */
    public static function load(array $init = []): Timber
    {
        if (self::$instance == null) {
            parent::init();

            if (isset($init['locations'])) {
                parent::$locations = $init['locations'];
            }

            if (isset($init['dirname'])) {
                parent::$dirname = $init['dirname'];
            }

            if (isset($init['cache'])) {
                parent::$cache = $init['cache'];
            }

            if (isset($init['autoescape'])) {
                parent::$autoescape = $init['autoescape'];
            }

            $timber = new Timber();
            $timber->init_constants();
            self::$instance = $timber;
        }

        return self::$instance;
    }
}
