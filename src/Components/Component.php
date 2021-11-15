<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components;

/**
 * Components build and render UI snippets to HTML.
 */
interface Component {

    /**
     * Render the component to HTML.
     *
     * @return string
     */
    public function render(): string;

}
