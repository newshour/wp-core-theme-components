<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Admin\Screens;

use WP_Screen;

/**
 * Screen classes provided added functionality to Wordpress admin screens. Each class that
 * implements ScreenInterface maps to a WP_Screen identifier (`id`).
 */
interface ScreenInterface
{
    /**
     * @return void
     */
    public function main(): void;

    /**
     * @param WP_Screen $screen
     * @return void
     */
    public function setWordpressScreen(WP_Screen $screen): void;

    /**
     * @return WP_Screen
     */
    public function getWordpressScreen(): WP_Screen;
}
