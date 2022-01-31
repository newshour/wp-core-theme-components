<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Admin\Screens;

use WP_Screen;

interface ScreenInterface
{
    /**
     * @return void
     */
    public function main(): void;

    /**
     * @param WP_Screen $screenName
     * @return void
     */
    public function setWordpressScreen(WP_Screen $screenName): void;

    /**
     * @return WP_Screen
     */
    public function getWordpressScreen(): WP_Screen;
}