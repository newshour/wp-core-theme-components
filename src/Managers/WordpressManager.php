<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Managers;

/**
 * Managers encapsulate Wordpress filters/actions and perform any other needed
 * intialization tasks. This allows for storage of all fitler/action callbacks
 * into organized units.
 */
interface WordpressManager
{
    /**
     * Runs any 'actions/triggers/tasks/etc' found in the page controller.
     *
     * @return void
     */
    public function run();
}
