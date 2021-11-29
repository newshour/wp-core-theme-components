<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Managers;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides common methods used by managers.
 *
 * @abstract
 */
abstract class Manager implements WordpressManager
{
    private Request $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Run the manager.
     *
     * @return void
     */
    abstract public function run(): void;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return self::class;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}
