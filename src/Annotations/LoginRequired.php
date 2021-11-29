<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Annotations;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides an annotation for controller methods to check for logged in users.
 *
 * @Annotation
 */
final class LoginRequired
{
    /**
     * Calls the Wordpress function is_user_logged_in().
     *
     * @see https://developer.wordpress.org/reference/functions/is_user_logged_in/
     * @return boolean
     */
    public function validateUser(): bool
    {
        return is_user_logged_in();
    }
}
