<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta\Schemas;

use NewsHour\WPCoreThemeComponents\Models\CorePost;

/**
 * Generates schema.org data for NewsArticle types.
 */
class NewsArticleSchema extends AbstractSchema
{
    public const SCHEMA_TYPE = 'NewsArticle';

    private string $headline = '';
    private string $dateline = '';

    /**
     * @param CorePost $post
     * @return NewsArticleSchema
     */
    public static function createFromCorePost(CorePost $post): NewsArticleSchema
    {
        $obj = new NewsArticleSchema();
        $obj->setUrl($post->link())
            ->setHeadline($post->title())
            ->setDescription($post->excerpt())
            ->setDatePublished($post->date('c'))
            ->setDateModified($post->modified_date('c'))
            ->setInLanguage(\get_locale());

        if (count($authors = $post->authors()) > 0) {
            foreach ($authors as $author) {
                if (is_object($author)) {
                    $obj->addAuthor(
                        PersonSchema::createFromTimberUser($author)
                    );
                }
            }
        }

        if (!empty($thumbnail = $post->thumbnail())) {
            $obj->setThumbnail(
                ImageSchema::createFromImageObj($thumbnail)
            );
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
     * Get the value of headline
     *
     * @return string
     */
    public function getHeadline(): string
    {
        return parent::parameters()->get('headline', '');
    }

    /**
     * Set the value of headline
     *
     * @return  self
     */
    public function setHeadline($headline): self
    {
        if (empty($headline)) {
            parent::parameters()->remove('headline');
        } else {
            parent::parameters()->set('headline', (string) $headline);
        }

        return $this;
    }

    /**
     * Get the value of dateline
     *
     * @return string
     */
    public function getDateline(): string
    {
        return parent::parameters()->get('dateline', '');
    }

    /**
     * Set the value of dateline
     *
     * @param string $dateline
     * @return self
     */
    public function setDateline($dateline): self
    {
        if (empty($headline)) {
            parent::parameters()->remove('dateline');
        } else {
            parent::parameters()->set('dateline', (string) $dateline);
        }

        return $this;
    }
}
