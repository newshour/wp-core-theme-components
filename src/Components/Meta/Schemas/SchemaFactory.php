<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

use NewsHour\WPCoreThemeComponents\Models\CorePost;
use Timber\Image;
use Timber\User;

/**
 * A factory for generating Schema objects.
 */
class SchemaFactory
{
    private static $instance;

    /**
     * @return SchemaFactory
     */
    public static function instance(): SchemaFactory
    {
        if (self::$instance == null) {
            self::$instance = new SchemaFactory();
        }

        return self::$instance;
    }

    /**
     * @param Image|null $image
     * @param string $size
     * @return ImageSchema
     */
    public function getImageSchema(Image $image = null, $size = 'large'): ImageSchema
    {
        if ($image != null) {
            return ImageSchema::createFromImageObj($image, $size);
        }

        return new ImageSchema();
    }

    /**
     * @param CorePost|null $corePost
     * @return NewsArticleSchema
     */
    public function getNewsArticleSchema(CorePost $corePost = null): NewsArticleSchema
    {
        if ($corePost != null) {
            return NewsArticleSchema::createFromCorePost($corePost);
        }

        return new NewsArticleSchema();
    }

    /**
     * @return OrganizationSchema
     */
    public function getOrganizationSchema($useBlogInfoValues = false): OrganizationSchema
    {
        if ($useBlogInfoValues) {
            return OrganizationSchema::createFromBlogInfo();
        }

        return new OrganizationSchema();
    }

    /**
     * @param User|null $user
     * @return PersonSchema
     */
    public function getPersonSchema(User $user = null): PersonSchema
    {
        if ($user != null) {
            return PersonSchema::createFromTimberUser($user);
        }

        return new PersonSchema();
    }

    /**
     * @return SearchActionSchema
     */
    public function getSearchActionSchema(): SearchActionSchema
    {
        return new SearchActionSchema();
    }

    /**
     * @param CorePost|null $corePost
     * @return WebPageSchema
     */
    public function getWebPageSchema(CorePost $corePost = null): WebPageSchema
    {
        if ($corePost != null) {
            return WebPageSchema::createFromCorePost($corePost);
        }

        return new WebPageSchema();
    }
}
