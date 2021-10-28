<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents;

/**
 * Utility methods.
 *
 * @final
 */
final class Utilities {

    /**
     * Performs a case-insensitive key check.
     *
     * @param mixed $key
     * @param array $array
     * @return boolean
     */
    public static function hasKey($key, array $array): bool {

        // Do some quick checks first.
        if (count($array) < 1) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        // ...now look for the key.
        $generator = fn ($array) => (yield from $array);

        foreach ($generator($array) as $k => $v) {
            if (is_string($k) && strcasecmp($k, $key) == 0) {
                return true;
            }
        }

        return false;
    }

}
