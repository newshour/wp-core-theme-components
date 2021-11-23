<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta;

use NewsHour\WPCoreThemeComponents\Components\Meta\Schemas\SchemaFactory;
use NewsHour\WPCoreThemeComponents\Models\CorePost;

/**
 * A factory for generating HtmlMeta objects.
 */
class MetaFactory
{
    private static $instance;

    /**
     * @return SchemaFactory
     */
    public static function instance(): MetaFactory
    {
        if (self::$instance == null) {
            self::$instance = new MetaFactory();
        }

        return self::$instance;
    }

    /**
     * @param CorePost|null $post
     * @param string $publisherUrl
     * @param string $section
     * @return FacebookMeta
     */
    public function getFacebookMeta(CorePost $post = null, $publisherUrl = '', $section = ''): FacebookMeta
    {
        if ($post != null) {
            return FacebookMeta::createFromCorePost($post, $publisherUrl, $section);
        }

        return new FacebookMeta();
    }

    /**
     * @param CorePost|null $post
     * @return PageMeta
     */
    public function getPageMeta(CorePost $post = null): PageMeta
    {
        if ($post != null) {
            return PageMeta::createFromCorePost($post);
        }

        return new PageMeta();
    }

    /**
     * @param CorePost|null $post
     * @param string $twitterHandle
     * @param boolean $useLargeImage
     * @return TwitterMeta
     */
    public function getTwitterMeta(CorePost $post = null, $twitterHandle = '', $useLargeImage = false): TwitterMeta
    {
        if ($post != null) {
            return TwitterMeta::createFromCorePost($post, $twitterHandle, $useLargeImage);
        }

        return new TwitterMeta();
    }

    /**
     * @return SchemaFactory
     */
    public function schemas(): SchemaFactory
    {
        return SchemaFactory::instance();
    }
}
