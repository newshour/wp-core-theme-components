<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Annotations;

use Symfony\Component\HttpFoundation\Request;

/**
 * @Annotation
 */
final class LoginRequired {

    /**
     * @param Request $request
     * @return boolean
     */
    public function validateUser(): bool {

        return is_user_logged_in();

    }

}
