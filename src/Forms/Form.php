<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Forms;

interface Form
{
    /**
     * Validates the submitted form.
     *
     * @return boolean
     */
    public function isValid(): bool;
}
