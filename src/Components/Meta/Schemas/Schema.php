<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

/**
 * schema.org data structures.
 */
interface Schema {

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
