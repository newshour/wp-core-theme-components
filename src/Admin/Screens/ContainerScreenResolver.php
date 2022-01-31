<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Admin\Screens;

use InvalidArgumentException;
use WP_Screen;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A resolver for Wordpress admin "screens" classes. These are classes that implement
 * ScreenInterface and can be used to extend the Wordpress admin.
 */
class ContainerScreenResolver
{
    protected ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param WP_Screen $wpScreen
     * @throws InvalidArgumentException;
     * @return Screen|null
     */
    public function getScreen(WP_Screen $wpScreen): ?ScreenInterface
    {
        try {
            $screen = $this->instantiateScreen($wpScreen);
        } catch (\InvalidArgumentException $ive) {
            throw $ive;
        }

        return $screen;
    }

    /**
     * @param WP_Screen $wpScreen
     * @throws InvalidArgumentException
     * @return ScreenInterface|null
     */
    private function instantiateScreen(WP_Screen $wpScreen): ?ScreenInterface
    {
        try {
            $foundClasses = $this->container->getParameter('registered_wp_screen_ids');
        } catch (InvalidArgumentException $iae) {
            return null;
        }

        $screenId = $wpScreen->id;

        if (!in_array($screenId, $foundClasses)) {
            return null;
        }

        $alias = 'wp.screen.' . str_replace('wp.screen.', '', $screenId);

        if ($this->container->has($alias)) {
            $obj = $this->container->get($alias);
            $obj->setWordpressScreen($wpScreen);
            return $obj;
        }

        throw new InvalidArgumentException(
            //phpcs:ignore
            "'{$screenId}' could not be mapped to a ScreenInterface. Please make sure `SCREEN_ID` returns a valid Wordpress screen identifier."
        );
    }
}
