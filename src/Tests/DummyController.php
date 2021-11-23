<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Tests;

use NewsHour\WPCoreThemeComponents\Annotations\HttpMethods;
use NewsHour\WPCoreThemeComponents\Annotations\LoginRequired;

class DummyController
{
    /**
     * @LoginRequired
     * @return void
     */
    public function loginRequiredMethod(): void
    {
        return;
    }

    /**
     * @HttpMethods("POST")
     * @return void
     */
    public function postRequiredMethod(): void
    {
        return;
    }
}
