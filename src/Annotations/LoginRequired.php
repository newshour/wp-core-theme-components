<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Annotations;

/**
 * Provides an annotation for controller methods to check for logged in users. If a capability is
 * passed to the constructor, the annotation will also check if the user has the capability.
 *
 * @Annotation
 * @NamedArgumentConstructor
 */
final class LoginRequired
{
    private string $capability = '';

    /**
     * @param string $capability
     */
    public function __construct($capability = '')
    {
        $this->capability = (string) $capability;
    }

    /**
     * @return string
     */
    public function getCapability(): string
    {
        return $this->capability;
    }

    /**
     * Calls the Wordpress function is_user_logged_in(). If a capability is set, also
     * checks the current user's capability.
     *
     * @see https://developer.wordpress.org/reference/functions/is_user_logged_in/
     * @see https://developer.wordpress.org/reference/functions/current_user_can/
     * @return boolean
     */
    public function validateUser(): bool
    {
        if (empty($this->capability)) {
            return is_user_logged_in();
        }

        if (is_user_logged_in()) {
            return current_user_can($this->capability);
        }

        return false;
    }
}
