<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta;

use NewsHour\WPCoreThemeComponents\Utilities;
use NewsHour\WPCoreThemeComponents\Models\CorePost;

/**
 * Represents Twitter meta tag data.
 */
class TwitterMeta extends HtmlMeta
{
    private string $site = '';
    private string $title = '';
    private string $imageUrl = '';
    private string $card = 'summary';
    private bool $doNotTrack = false;

    public function __construct()
    {
        if (!empty((int) get_option('core_theme_twitter_do_not_track', 0))) {
            $this->doNotTrack = true;
        }
    }

    /**
     * @param CorePost $post
     * @return TwitterMeta
     */
    public static function createFromCorePost(CorePost $post): TwitterMeta
    {
        $obj = new TwitterMeta();
        $obj->setTitle($post->title())
            ->setSite(get_option('core_theme_twitter_handle', ''));

        if ((bool) get_option('core_theme_twitter_large_image')) {
            $obj->setCard('summary_large_image');
        }

        if (!empty($thumbnail = $post->thumbnail())) {
            $obj->setImageUrl($thumbnail->src('large'));
        }

        return $obj;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $html = [
            Utilities::createMetaPropertyTag('twitter:site', $this->getSite()),
            Utilities::createMetaPropertyTag('twitter:title', $this->getTitle()),
            Utilities::createMetaPropertyTag('twitter:card', $this->getCard()),
            Utilities::createMetaPropertyTag('twitter:image', $this->getImageUrl())
        ];

        if ($this->getDoNotTrack()) {
            $html[] = Utilities::createMetaPropertyTag('twitter:dnt', 'on');
        }

        $html = array_filter($html);

        return implode(PHP_EOL, $html);
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
        $this->title = (string) $title;

        return $this;
    }

    /**
     * Get the value of the Twitter handle.
     *
     * @return string
     */
    public function getSite(): string
    {
        return $this->site;
    }

    /**
     * Set the Twitter handle.
     *
     * @param string $site
     * @return self
     */
    public function setSite($site): self
    {
        if (!empty($site)) {
            $this->site = '@' . ltrim($site, '@');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCard(): string
    {
        return $this->card;
    }

    /**
     * @param string $card
     * @return self
     */
    public function setCard($card): self
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     * @return void
     */
    public function setImageUrl($imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDoNotTrack(): bool
    {
        return $this->doNotTrack;
    }

    /**
     * @param bool $doNotTrack
     * @return self
     */
    public function setDoNotTrack($doNotTrack): self
    {
        if (is_string($doNotTrack) && strcasecmp($doNotTrack, 'on') == 0) {
            $doNotTrack = true;
        }

        $this->doNotTrack = (bool) $doNotTrack;

        return $this;
    }
}
