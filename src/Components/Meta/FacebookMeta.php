<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta;

use Carbon\Carbon;
use NewsHour\WPCoreThemeComponents\Utilities;
use NewsHour\WPCoreThemeComponents\Models\CorePost;

/**
 * Represents Facebook meta tag data.
 */
class FacebookMeta extends HtmlMeta
{
    private array $appId = [];
    private array $pagesId = [];
    private string $publisherUrl = '';
    private string $siteName = '';
    private string $type = 'website';
    private string $title = '';
    private string $description = '';
    private string $imageUrl = '';
    private int $imageHeight = 0;
    private int $imageWidth = 0;
    private string $url = '';
    private string $section = '';
    private array $tags = [];

    private ?Carbon $publishedOn = null;
    private ?Carbon $modifiedOn = null;

    /**
     * @param CorePost $post
     * @param string $contentType
     * @param string $section
     * @return FacebookMeta
     */
    public static function createFromCorePost(CorePost $post, $contentType = 'article', $section = ''): FacebookMeta
    {
        $obj = new FacebookMeta();
        $obj->setType($contentType)
            ->setTitle($post->title())
            ->setDescription($post->excerpt())
            ->setUrl($post->link())
            ->setTags($post->tags())
            ->setPublishedOn($post->date('c'))
            ->setModifiedOn($post->modified_date('c'))
            ->setSection($section)
            ->setSiteName(get_bloginfo('name'));

        if (!empty($facebookUrl = get_option('core_theme_facebook_page_url', ''))) {
            $obj->setPublisherUrl($facebookUrl);
        }

        if (!empty($appIds = get_option('core_theme_facebook_app_id', ''))) {
            $obj->setAppId(
                explode(',', str_replace(', ', ',', $appIds))
            );
        }

        if (!empty($pagesIds = get_option('core_theme_facebook_page_id', ''))) {
            $obj->setPagesId(
                explode(',', str_replace(', ', ',', $pagesIds))
            );
        }

        if (!empty($thumbnail = $post->thumbnail())) {
            $obj->setImageUrl(
                $thumbnail->src('large')
            )->setImageHeight(
                Utilities::getImageDimension($thumbnail, 'large', 'height')
            )->setImageWidth(
                Utilities::getImageDimension($thumbnail, 'large', 'width')
            );
        }

        return $obj;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $html = [
            Utilities::createMetaPropertyTag('og:locale', get_locale()),
            Utilities::createMetaPropertyTag('og:type', $this->getType()),
            Utilities::createMetaPropertyTag('og:site_name', $this->getSiteName()),
            Utilities::createMetaPropertyTag('og:title', $this->getTitle()),
            Utilities::createMetaPropertyTag('og:description', $this->getDescription()),
            Utilities::createMetaPropertyTag('og:url', $this->getUrl()),
            Utilities::createMetaPropertyTag('og:image', $this->getImageUrl()),
            Utilities::createMetaPropertyTag('og:image:height', $this->getImageHeight()),
            Utilities::createMetaPropertyTag('og:image:width', $this->getImageWidth()),
            Utilities::createMetaPropertyTag('article:section', $this->getSection()),
            Utilities::createMetaPropertyTag('article:publisher', $this->getPublisherUrl())
        ];

        if (!empty($pubDate = $this->getPublishedOn())) {
            $html[] = Utilities::createMetaPropertyTag('article:published_time', $pubDate->format('c'));
        }

        if (!empty($modDate = $this->getModifiedOn())) {
            $html[] = Utilities::createMetaPropertyTag('article:modified_time', $modDate->format('c'));
        }

        if (count($this->getTags()) > 0) {
            $html[] = Utilities::createMetaPropertyTag('article:tag', implode(', ', $this->getTags()));
        }

        if (count($this->getAppId()) > 0) {
            foreach ($this->getAppId() as $id) {
                $html[] = Utilities::createMetaPropertyTag('fb:app_id', $id);
            }
        }

        if (count($this->getPagesId()) > 0) {
            foreach ($this->getPagesId() as $id) {
                $html[] = Utilities::createMetaPropertyTag('fb:pages', $id);
            }
        }

        $html = array_filter($html);

        if (count($html) < 1) {
            return '';
        }

        return implode(PHP_EOL, $html);
    }

    /**
     * @return array
     */
    public function getAppId(): array
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     * @return self
     */
    public function addAppId($appId): self
    {
        if (!in_array($appId, $this->appId)) {
            $this->appId[] = $appId;
        }

        return $this;
    }

    /**
     * @param array $appId
     * @return self
     */
    public function setAppId(array $appId = []): self
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * @return array
     */
    public function getPagesId(): array
    {
        return $this->pagesId;
    }

    /**
     * @param string $pagesId
     * @return self
     */
    public function addPagesId($pagesId): self
    {
        if (!in_array($pagesId, $this->pagesId)) {
            $this->pagesId[] = $pagesId;
        }

        return $this;
    }

    /**
     * @param array $pagesId
     * @return self
     */
    public function setPagesId(array $pagesId = []): self
    {
        $this->pagesId = $pagesId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublisherUrl(): string
    {
        return $this->publisherUrl;
    }

    /**
     * @param string $publisherUrl
     * @return self
     */
    public function setPublisherUrl($publisherUrl): self
    {
        $this->publisherUrl = $publisherUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteName(): string
    {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     * @return self
     */
    public function setSiteName($siteName): self
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return self
     */
    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription($description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     *
     * @param string $imageUrl
     * @return self
     */
    public function setImageUrl($imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    /**
     *
     * @param integer $imageHeight
     * @return self
     */
    public function setImageHeight($imageHeight): self
    {
        $this->imageHeight = (int)$imageHeight;

        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    /**
     *
     * @param integer $imageWidth
     * @return self
     */
    public function setImageWidth($imageWidth): self
    {
        $this->imageWidth = (int)$imageWidth;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     *
     * @param string $url
     * @return self
     */
    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getSection(): string
    {
        return $this->section;
    }

    /**
     *
     * @param string $section
     * @return self
     */
    public function setSection($section): self
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Add a tag.
     *
     * @param string $tag
     * @return self
     */
    public function addTag($tag): self
    {
        $this->tags[] = str_replace(',', ' ', trim($tag));

        return $this;
    }

    /**
     * Add a list tags. $tags may also be set as a single string or tokenized (e.g. CSV, piped).
     *
     * @param array|string $tags
     * @param string $token Optional
     * @return self
     */
    public function setTags($tags, $token = null): self
    {
        $tagList = [];

        if (is_array($tags)) {
            $tagList = $tags;
        } elseif (!empty($token) && strpos($token, $tags) !== false) {
            $tagList = Utilities::splitter($tags, $token);
        } else {
            $tagList = [$tags];
        }

        foreach ($tagList as $tag) {
            $this->addTag($tag);
        }

        return $this;
    }


    /**
     * Get the value of publishedOn
     *
     * @return Carbon|null
     */
    public function getPublishedOn(): ?Carbon
    {
        return $this->publishedOn;
    }

    /**
     * Set the value of publishedOn
     *
     * @param Carbon|string|int $publishedOn
     * @param string $timezone Optional, default will be value of wp_timezone().
     * @return self
     */
    public function setPublishedOn($publishedOn, $timezone = '')
    {
        if (empty($publishedOn)) {
            return $this;
        }

        if (empty($timezone)) {
            $timezone = \wp_timezone();
        }

        if ($publishedOn instanceof Carbon) {
            $this->publishedOn = $publishedOn;
        } elseif (is_numeric($publishedOn)) {
            $this->publishedOn = Carbon::createFromTimestamp(
                $publishedOn,
                $timezone
            );
        } else {
            $this->publishedOn = Carbon::createFromTimestamp(
                strtotime($publishedOn),
                $timezone
            );
        }

        return $this;
    }

    /**
     * Get the value of modifiedOn
     *
     * @return Carbon|null
     */
    public function getModifiedOn(): ?Carbon
    {
        return $this->modifiedOn;
    }

    /**
     * Set the value of modifiedOn
     *
     * @param Carbon|string|int $modifiedOn
     * @param string $timezone Optional, default will be value of wp_timezone().
     * @return self
     */
    public function setModifiedOn($modifiedOn, $timezone = '')
    {
        if (empty($modifiedOn)) {
            return $this;
        }

        if (empty($timezone)) {
            $timezone = \wp_timezone();
        }

        if ($modifiedOn instanceof Carbon) {
            $this->modifiedOn = $modifiedOn;
        } elseif (is_numeric($modifiedOn)) {
            $this->modifiedOn = Carbon::createFromTimestamp(
                $modifiedOn,
                $timezone
            );
        } else {
            $this->modifiedOn = Carbon::createFromTimestamp(
                strtotime($modifiedOn),
                $timezone
            );
        }

        return $this;
    }
}
