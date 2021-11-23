<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Managers;

/**
 * Managers encapsulate various bootstrap routines, configurations and settings for Wordpress by
 * running any assigned tasks.
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
