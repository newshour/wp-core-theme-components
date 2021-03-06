<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Models;

use Carbon\Carbon;
use Timber\Post;
use Timber\TextHelper;

/**
 * Provides common methods for CorePost objects.
 *
 * @abstract
 */
abstract class CorePost extends Post
{
    // Storage for categories and tags.
    private array $storage = [];

    /**
     * @var Carbon|null
     */
    private ?Carbon $modifiedDateObj = null;

    /**
     * @var Carbon|null
     */
    private ?Carbon $publishedDateObj = null;

    /**
     * Get the post excerpt by returning the `post_excerpt` property set by Wordpress. If this property
     * is not set, the content is truncated and used as the excerpt.
     *
     * @return string
     */
    public function excerpt(): string
    {
        if (empty($this->post_excerpt)) {
            return TextHelper::trim_words($this->content(), 55, '...', '');
        }

        return strip_tags($this->post_excerpt);
    }

    /**
     * Overrides parent::categories() so that we store the data.
     *
     * @return array
     */
    public function categories(): array
    {
        if (empty($this->storage['categories'])) {
            $this->storage['categories'] = parent::categories();
        }

        return $this->storage['categories'];
    }

    /**
     * Pulls fresh category data. Heads up, this incurs a db hit.
     *
     * @return CorePost
     */
    public function refreshCategories(): CorePost
    {
        $this->storage['categories'] = null;
        $this->categories();
        return $this;
    }

    /**
     * Overrides parent::tags() so that we store the data.
     *
     * @return array
     */
    public function tags(): array
    {
        if (empty($this->storage['tags'])) {
            $this->storage['tags'] = parent::tags();
        }

        return $this->storage['tags'];
    }

    /**
     * Pulls fresh tag data. Heads up, this incurs a db hit.
     *
     * @return CorePost
     */
    public function refreshTags(): CorePost
    {
        $this->storage['tags'] = null;
        $this->tags();
        return $this;
    }

    /**
      * Returns the published date as a Carbon obj.
      *
      * @return Carbon|null
      */
    public function getPublishedDate(): ?Carbon
    {
        if (empty($this->publishedDateObj) && !empty($date = $this->date('c'))) {
            $this->publishedDateObj = Carbon::createFromTimestamp(
                strtotime($date),
                wp_timezone()
            );
        }

        return $this->publishedDateObj;
    }

    /**
     * Returns the modifed date as a Carbon obj.
     *
     * @return Carbon|null
     */
    public function getModifiedDate(): ?Carbon
    {
        if (empty($this->modifiedDateObj) && !empty($date = $this->modified_date('c'))) {
            $this->modifiedDateObj = Carbon::createFromTimestamp(
                strtotime($date),
                wp_timezone()
            );
        }

        return $this->modifiedDateObj;
    }
}
