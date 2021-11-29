<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components\Meta;

use NewsHour\WPCoreThemeComponents\Utilities;
use NewsHour\WPCoreThemeComponents\Models\CorePost;

/**
 * Generates meta tags for the <head> section of a web page.
 *
 * @version 1.0.0
 */
class PageMeta extends HtmlMeta
{
    private string $canonicalUrl = '';
    private string $description = '';
    private string $robots = '';
    private string $podcastFeed = '';
    private string $articleFeed = '';
    private array $keywords = [];
    private bool $withKeywords = false;

    private ?FacebookMeta $facebookMeta = null;
    private ?TwitterMeta $twitterMeta = null;

    /**
     * @param FacebookMeta|null $facebookMeta
     * @param TwitterMeta|null $twitterMeta
     */
    public function __construct(FacebookMeta $facebookMeta = null, TwitterMeta $twitterMeta = null)
    {
        if (!$facebookMeta == null) {
            $this->setFacebookMeta($facebookMeta);
        }

        if (!$twitterMeta == null) {
            $this->setTwitterMeta($twitterMeta);
        }
    }

    /**
     * @param CorePost $post
     * @return PageMeta
     */
    public static function createFromCorePost(CorePost $post): PageMeta
    {
        $facebookType = ($post->post_type == 'page') ? 'website' : 'article';
        $facebookMeta = FacebookMeta::createFromCorePost($post, $facebookType);
        $twitterMeta = TwitterMeta::createFromCorePost($post);

        $obj = new PageMeta($facebookMeta, $twitterMeta);
        $obj->setCanonicalUrl($post->link())
            ->setDescription($post->excerpt())
            ->setKeywords($post->tags());

        return $obj;
    }

    /**
     * @return string
     */
    public function render(): string
    {
        // Add metas.
        $html = [
            Utilities::createMetaTag('description', $this->getDescription()),
            Utilities::createMetaTag('robots', $this->getRobots())
        ];

        // Add keywords if requested.
        if ($this->getwithKeywords() && count($keywords = $this->getKeywords()) > 0) {
            Utilities::createMetaTag('keywords', implode(', ', $keywords));
        }

        // Add links.
        $html[] = Utilities::createLinkTag('canonical', $this->getCanonicalUrl());
        $html[] = Utilities::createLinkTag('alternate', $this->getPodcastFeed(), 'application/rss+xml');
        $html[] = Utilities::createLinkTag('alternate', $this->getArticleFeed(), 'application/rss+xml');

        // Remove empties.
        $html = array_filter($html);
        $htmlStr = implode(PHP_EOL, $html);

        // Add Social metas.
        if (!empty($facebookMeta = $this->getFacebookMeta())) {
            $htmlStr .= PHP_EOL . (string) $facebookMeta;
        }

        if (!empty($twitterMeta = $this->getTwitterMeta())) {
            $htmlStr .= PHP_EOL . (string) $twitterMeta;
        }

        return $htmlStr;
    }

    /**
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        return $this->canonicalUrl;
    }

    /**
     * @return self
     */
    public function setCanonicalUrl($canonicalUrl): self
    {
        $this->canonicalUrl = $canonicalUrl;

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
     * @return self
     */
    public function setDescription($description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getRobots(): string
    {
        return $this->robots;
    }

    /**
     * @return self
     */
    public function setRobots($robots): self
    {
        $this->robots = $robots;
        return $this;
    }

    /**
     * Returns a list of strings (keywords).
     *
     * @return string[]
     */
    public function getKeywords(): array
    {
        return $this->keywords;
    }

    /**
     * Add a keyword.
     *
     * @param string $tag
     * @return self
     */
    public function addKeyword($keyword): self
    {
        $cleaned = trim((string) $keyword);

        if (!in_array($cleaned, $this->keywords)) {
            $this->keywords[] = $cleaned;
        }

        return $this;
    }

    /**
     * Add a list keywords. $keywords may also be set as a single string or tokenized (e.g. CSV, piped).
     *
     * @param array|string $keywords
     * @param string $token Optional
     * @return self
     */
    public function setKeywords($keywords, $token = ''): self
    {
        $keywordsList = [];

        if (is_array($keywords)) {
            $keywordsList = $keywords;
        } elseif (!empty($token) && strpos($token, $keywords) !== false) {
            $keywordsList = Utilities::splitter($keywords, $token);
        } else {
            $keywordsList = [$keywords];
        }

        if (count($keywordsList) > 0) {
            foreach ($keywordsList as $keywords) {
                $this->addKeyword($keywords);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPodcastFeed(): string
    {
        return $this->podcastFeed;
    }

    /**
     * @return self
     */
    public function setPodcastFeed($podcastFeed): self
    {
        $this->podcastFeed = $podcastFeed;

        return $this;
    }

    /**
     * @return string
     */
    public function getArticleFeed(): string
    {
        return $this->articleFeed;
    }

    /**
     * @return self
     */
    public function setArticleFeed($articleFeed): self
    {
        $this->articleFeed = $articleFeed;

        return $this;
    }

    /**
     * @return FacebookMeta|null
     */
    public function getFacebookMeta(): ?FacebookMeta
    {
        return $this->facebookMeta;
    }

    /**
     * @param FacebookMeta $facebookMeta
     * @return self
     */
    public function setFacebookMeta(FacebookMeta $facebookMeta): self
    {
        $this->facebookMeta = $facebookMeta;

        return $this;
    }

    /**
     * @return TwitterMeta|null
     */
    public function getTwitterMeta(): ?TwitterMeta
    {
        return $this->twitterMeta;
    }

    /**
     * @param TwitterMeta $twitterMeta
     * @return self
     */
    public function setTwitterMeta(TwitterMeta $twitterMeta): self
    {
        $this->twitterMeta = $twitterMeta;

        return $this;
    }

    /**
     * Returns true if keywords meta tag is to be added.
     *
     * @return bool
     */
    public function getWithKeywords(): bool
    {
        return $this->withKeywords;
    }

    /**
     * Adds keywords meta tag if $withKeywords is true.
     *
     * @param bool $withKeywords
     * @return self
     */
    public function setWithKeywords($withKeywords): self
    {
        $this->withKeywords = (bool) $withKeywords;

        return $this;
    }
}
