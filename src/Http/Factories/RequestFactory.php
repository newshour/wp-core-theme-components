<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Http\Factories;

use Symfony\Component\HttpFoundation\InputBag;
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

            // Fixes for Wordpress's auto-escaping mutations.
            $request = new Request(
                stripslashes_deep($_GET),
                stripslashes_deep($_POST),
                [],
                stripslashes_deep($_COOKIE),
                $_FILES,
                stripslashes_deep($_SERVER)
            );

            // Borrowed from Request's `createFromGlobals` method.
            if (str_starts_with($request->headers->get('CONTENT_TYPE', ''), 'application/x-www-form-urlencoded')
                && \in_array(strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])
            ) {
                parse_str($request->getContent(), $data);
                $request->request = new InputBag($data);
            }

            $request->setLocale(get_locale());
            $request->setDefaultLocale(get_locale());

            $stack = new RequestStack();
            $stack->push($request);
            self::$stack = $stack;
        }

        return self::$stack;
    }
}
