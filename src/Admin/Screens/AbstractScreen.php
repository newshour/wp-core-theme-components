<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Admin\Screens;

use WP_Screen;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractScreen implements ScreenInterface, ContainerAwareInterface, LoggerAwareInterface
{
    private ?WP_Screen $wpScreen = null;
    private ?ContainerInterface $container = null;
    private ?LoggerInterface $logger = null;

    /**
     * @return void
     */
    abstract public function main(): void;

    /**
     * @param WP_Screen $wpScreen
     * @return void
     */
    public function setWordpressScreen(WP_Screen $wpScreen): void
    {
        $this->wpScreen = $wpScreen;
    }

    /**
     * @return WP_Screen
     */
    public function getWordpressScreen(): WP_Screen
    {
        return $this->wpScreen;
    }

    /**
     * @return ContainerInterface|null
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ContainerInterface|null $container
     * @return void
     */
    public function setContainer(ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
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
