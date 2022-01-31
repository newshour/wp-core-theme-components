<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Managers;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides common methods used by managers.
 *
 * @abstract
 */
abstract class Manager implements WordpressManager, LoggerAwareInterface
{
    protected ?LoggerInterface $logger = null;
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

    /**
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
