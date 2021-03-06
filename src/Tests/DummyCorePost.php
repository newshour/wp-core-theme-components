<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests;

use NewsHour\WPCoreThemeComponents\Models\CorePost;

class DummyCorePost extends CorePost
{
    public function __construct()
    {
        $GLOBALS['wp_query'] = new \stdClass();
        $GLOBALS['wp_query']->is_home = false;
    }

    /**
     * @param string $format
     * @return string
     */
    public function date($format = 'c'): string
    {
        return date($format);
    }

    /**
     * @param string $format
     * @return string
     */
    public function modified_date($format = 'c'): string
    {
        return date($format);
    }

    /**
     * @return array
     */
    public function categories(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function excerpt(): string
    {
        return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
    }

    /**
     * @return array
     */
    public function tags(): array
    {
        return [];
    }
}
