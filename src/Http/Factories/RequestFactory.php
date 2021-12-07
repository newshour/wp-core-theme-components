<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Http\Factories;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Retrieves the Request object. The Request object is a singleton created
 * by using PHP's super globals.
 *
 * @see https://symfony.com/doc/current/components/http_foundation.html#request
 */
final class RequestFactory
{
    /**
     * @var Request
     */
    private static $instance;

    /**
     * @var RequestStack
     */
    private static $stack;

    /**
     * @return Request
     */
    public static function get(): Request
    {
        if (self::$instance == null) {
            self::$instance = self::getStack()->getCurrentRequest();
        }

        return self::$instance;
    }

    /**
     * @return RequestStack
     */
    public static function getStack(): RequestStack
    {
        if (self::$stack == null) {
            $request = Request::createFromGlobals();
            $request->setLocale(get_locale());
            $request->setDefaultLocale(get_locale());
            $stack = new RequestStack();
            $stack->push($request);
            self::$stack = $stack;
        }

        return self::$stack;
    }
}
