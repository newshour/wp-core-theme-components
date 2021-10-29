<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Annotations;

use Symfony\Component\HttpFoundation\Request;

/**
 * @Annotation
 * @NamedArgumentConstructor
 */
final class HttpMethods {

    public $methods = [];

    /**
     * @param array $methods
     */
    public function __construct($methods = []) {

        $_methods = is_array($methods) ? $methods : [$methods];
        $this->setMethods($_methods);

    }

    /**
     * Get the value of methods
     */
    public function getMethods(): array {

        return $this->methods;

    }

    /**
     * Set the value of methods
     *
     * @return HttpMethods
     */
    public function setMethods($methods): HttpMethods {

        $this->methods = is_array($methods) ? $methods : [$methods];
        return $this;

    }

    /**
     * @param Request $request
     * @return boolean
     */
    public function validateMethods(Request $request): bool {

        $allowed = array_map('strtoupper', $this->getMethods());

        if (in_array($request->getMethod(), $allowed)) {
            return true;
        }

        if (in_array('SAFE', $allowed)) {
            return $request->isMethodSafe();
        }

        if (in_array('IDEMPOTENT', $allowed)) {
            return $request->isMethodIdempotent();
        }

        if (in_array('CACHEABLE', $allowed)) {
            return $request->isMethodCacheable();
        }

        return false;

    }

}
