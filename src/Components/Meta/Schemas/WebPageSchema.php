<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

use NewsHour\WPCoreThemeComponents\Models\CorePost;

/**
 * Generates schema.org data for WebPage types.
 */
class WebPageSchema extends AbstractSchema
{
    public const SCHEMA_TYPE = 'WebPage';

    /**
     * @param CorePost $post
     * @param OrganizationSchema $publisher Optional
     * @return WebPageSchema
     */
    public static function createFromCorePost(CorePost $post, OrganizationSchema $publisher = null): WebPageSchema
    {
        if ($publisher == null) {
            $publisher = OrganizationSchema::createFromBlogInfo();
        }

        if (!empty($thumbnail = $post->thumbnail())) {
            $thumbnail = ImageSchema::createFromImageObj($thumbnail);
        } else {
            $thumbnail = new ImageSchema();
        }

        $obj = new WebPageSchema();
        $obj->setUrl($post->link())
            ->setName($post->title())
            ->setDescription($post->excerpt())
            ->setDatePublished($post->date('c'))
            ->setDateModified($post->modified_date('c'))
            ->setInLanguage(\get_locale())
            ->setThumbnail($thumbnail)
            ->setPublisher($publisher);

        if (count($authors = $post->authors()) > 0) {
            foreach ($authors as $author) {
                $obj->addAuthor(
                    PersonSchema::createFromTimberUser($author)
                );
            }
        }

        return $obj;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $headers = [
            '@context' => 'http://schema.org',
            '@type' => self::SCHEMA_TYPE
        ];

        if (!empty($url = parent::getUrl())) {
            $headers['mainEntityOfPage'] = $url;
        }

        return array_merge($headers, parent::toArray());
    }

    /**
     * Get the value of name
     *
     * @return string
     */
    public function getName(): string
    {
        return parent::parameters()->get('name', '');
    }

    /**
     * Set the value of name
     *
     * @param string $name
     * @return self
     */
    public function setName($name): self
    {
        if (empty($name)) {
            parent::parameters()->remove('name');
        } else {
            parent::parameters()->set('name', (string) $name);
        }

        return $this;
    }
}
