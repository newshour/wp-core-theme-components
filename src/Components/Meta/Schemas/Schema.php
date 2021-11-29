<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

/**
 * Generates schema.org data which is added to the <head> element of web pages.
 */
interface Schema
{
    /**
     * Return the schema data as a dictionary.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Returns true if the Schema object is considered "empty".
     *
     * @return boolean
     */
    public function isEmpty(): bool;
}
