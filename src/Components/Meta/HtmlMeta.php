<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta;

use NewsHour\WPCoreThemeComponents\Components\Component;

/**
 * Generates meta tags for the <head> section.
 *
 * @version 1.0.0
 */
abstract class HtmlMeta implements Component
{
    /**
     * @return string
     */
    abstract public function render(): string;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }
}
