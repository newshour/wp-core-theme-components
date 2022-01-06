<?php

/**
 * @version 1.0.1
 */

namespace NewsHour\WPCoreThemeComponents\Query;

use UnexpectedValueException;
use WP_Query;
use Carbon\Carbon;
use Timber\PostQuery;

/**
 * Provides a fluent interface for fetching data from WP.
 *
 * For example, to retrieve the 10 latest posts of a given Model class:
 *
 * ```
 * Model::objects()->filter(['posts_per_page' => 10])->orderBy('post_date')->desc()->get();
 * ```
 *
 * or even more concise:
 *
 * ```
 * Model::objects()->latest(10)->get();
 * ```
 *
 * @final
 */
final class PostsResultSet implements ResultSet
{
    // Stores the cache expires time in seconds. -1 is no cache, 0 is cache forever.
    private int $cacheInSeconds = -1;

    // The model class that Timber will use to instantiate.
    private string $postClass = '';

    // Stores the query params.
    private array $queryParams = [];

    // Stores the data.
    private array $data = [];

    /**
     * @param string $postClass
     * @param array $queryParams
     */
    private function __construct(string $postClass = '', array $queryParams = [])
    {
        $initial = [
            'order' => 'DESC',
            'orderby' => 'ID',
            'post_status' => 'publish',
            'no_found_rows' => true
        ];

        $this->postClass = empty($postClass) ? '\Timber\Post' : $postClass;
        $this->queryParams = array_merge($initial, $queryParams);
        $this->queryParams['post_type'] = $this->getPostTypeValue($postClass);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s<%s> [query params: %s]',
            self::class,
            $this->postClass,
            http_build_query($this->queryParams)
        );
    }

    /**
     * Creates a ResultSet instance.
     *
     * @param string $postClass
     * @param array $params
     * @return self
     */
    public static function factory($postClass = '', array $params = []): self
    {
        return new PostsResultSet($postClass, $params);
    }

    // ------------------------------------------------------------------------
    // Methods that WILL hit the database.
    // ------------------------------------------------------------------------

    /**
     * Returns the result set array based on the set query params. This method
     * will hit the database.
     *
     * @category Database Read
     * @return array
     */
    public function get(): array
    {
        if (!empty($this->data)) {
            return $this->data;
        }

        $useCache = $this->cacheInSeconds > -1 ? true : false;

        if ($useCache) {
            $cacheKey = __FUNCTION__ . http_build_query($this->queryParams);
            $cacheGroup = __CLASS__ . "<{$this->postClass}>";
            $cachedPosts = wp_cache_get($cacheKey, $cacheGroup);

            if ($cachedPosts !== false && count($cachedPosts) > 0) {
                $this->data = $cachedPosts;
                return $this->data;
            }
        }

        // If 'fields' was passed and is not set to 'all', we need to use the WP_Query class.
        if (!empty($this->queryParams['fields']) && strcasecmp($this->queryParams['fields'], 'all') != 0) {
            $this->data = (new WP_Query($this->queryParams))->get_posts();
        } else {
            $this->data = (new PostQuery($this->queryParams, $this->postClass))->get_posts();
        }

        if ($useCache && count($this->data) > 0) {
            wp_cache_set($cacheKey, $this->data, $cacheGroup);
        }

        return $this->data;
    }

    /**
     * Returns the first result by primary key (post ID). This method will
     * hit the database.
     *
     * @throws UnexpectedValueException
     * @param int $pk
     * @return null|object
     */
    public function pk($pk): ?object
    {
        $this->queryParams = [
            'p' => is_numeric($pk) ? (int) $pk : 0
        ];

        $result = $this->limit(1)->get();

        if (count($result) > 1) {
            throw new UnexpectedValueException('Multiple records found.');
        }

        return isset($result[0]) ? $result[0] : null;
    }

    /**
     * Alias for pk(int $pk), but also accepts an array of IDs.
     *
     * @param array|int $pid The post ID(s).
     * @return array
     */
    public function id($pid)
    {
        if (is_array($pid) && count($pid) > 0) {
            $clean = array_filter($pid, 'is_numeric');
            $cleanCount = count($clean);

            if ($cleanCount == 0) {
                return [];
            }

            if ($cleanCount == 1) {
                return $this->pk($clean[0]);
            }

            $this->queryParams['post__in'] = $clean;
            $this->queryParams['ignore_sticky_posts'] = true;

            return $this->get();
        }

        if (is_numeric($pid)) {
            try {
                return [$this->pk($pid)];
            } catch (UnexpectedValueException $uve) {
                error_log($uve->getMessage());
            }
        }

        return [];
    }

    /**
     * Returns all records in the collection. This method will hit the database.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->limit(-1)->get();
    }

    /**
     * Retrieve only the first result. This method will hit the database.
     *
     * @category Database Read
     * @return array
     */
    public function first(): array
    {
        return $this->limit(1)->get();
    }

    /**
     * Returns a slice of the collection starting at the given index.
     * Similar to Laravel's slice(). This method will hit the database.
     *
     * @category Database Read
     * @param int $start
     * @return array
     */
    public function slice($start): array
    {
        $localArray = $this->get();

        if (count($localArray) < 1) {
            return [];
        }

        return array_slice($localArray, $start);
    }

    /**
     * Shuffles (and slices) the result set. This method will hit the database.
     *
     * @category Database Read
     * @param integer $andSlice Optional
     * @return array
     */
    public function shuffle($andSlice = 0): array
    {
        $localArray = $this->get();

        if (count($localArray) < 1) {
            return [];
        }

        shuffle($localArray);

        if ($andSlice < 1) {
            return $localArray;
        }

        return array_slice($localArray, 0, $andSlice);
    }

    // ------------------------------------------------------------------------
    // Methods that WILL NOT hit the database.
    // ------------------------------------------------------------------------

    /**
     * Retrieve results with any status.
     *
     * @return self
     */
    public function any(): self
    {
        $this->queryParams['post_status'] = 'any';
        return $this;
    }

    /**
     * @return self
     */
    public function asc(): self
    {
        return $this->order('ASC');
    }

    /**
     * Sets the cache expires time in seconds. -1 is no cache, 0 is
     * cache forever.
     *
     * @param int $seconds
     * @return self
     */
    public function cache($seconds): self
    {
        $this->cacheInSeconds = (int)$seconds < 0 ? -1 : (int)$seconds;
        return $this;
    }

    /**
     * Sets the cache to store forever.
     *
     * @return self
     */
    public function cacheForever(): self
    {
        return $this->cache(0);
    }

    /**
     * Filter by date range. The default is to use 'inclusive' dates.
     *
     * @param Carbon $start
     * @param Carbon|null $end Optional
     * @param boolean $inclusive Optional, default is true.
     * @return self
     */
    public function dateRange(Carbon $start, Carbon $end = null, $inclusive = true): self
    {
        $dateQuery = [
            'inclusive' => (bool) $inclusive,
            'after' => $start->toIso8601String()
        ];

        if ($end != null) {
            $dateQuery['before'] = $end->toIso8601String();
        }

        $this->queryParams['date_query'] = $dateQuery;
        $this->limit(-1);

        return $this;
    }

    /**
     * @return self
     */
    public function desc(): self
    {
        return $this->order('DESC');
    }

    /**
     * Exclude by ID or parent ID.
     *
     * @param array $excludeIds
     * @param boolean $parent If true, excludes by parent ID(s).
     * @return self
     */
    public function exclude(array $ids, $parent = false): self
    {
        if (count($ids) < 1) {
            return $this;
        }

        $clean = array_filter($ids, 'is_numeric');

        if (count($clean) < 1) {
            return $this;
        }

        $key = $parent ? 'post_parent__not_in' : 'post__not_in';
        $this->queryParams[$key] = $clean;

        return $this;
    }

    /**
     * Filter a query by keyword args. See WP_Query documentation for a full
     * list of args. You do not need to pass the 'post_type' parameter as this
     * will automatically be done based on the model class mappings in
     * TimberManager::classMap().
     *
     * @see TimberManager
     * @param array $params
     * @return self
     */
    public function filter(array $params): self
    {
        if (isset($params['post_type'])) {
            $message = 'Setting the "post_type" parameter as a filter query is redundant.';

            if (strcasecmp($params['post_type'], $this->queryParams['post_type']) != 0) {
                $message = sprintf(
                    'You cannot filter on a different "post type". %s maps to "%s".',
                    $this->postClass,
                    $this->queryParams['post_type']
                );
            }

            unset($params['post_type']);
            trigger_error($message);
        }

        $this->queryParams = array_merge($this->queryParams, $params);
        return $this;
    }

    /**
     * Sets the `fields` parameter to `ids`.
     *
     * @return self
     */
    public function idsOnly(): self
    {
        $this->queryParams['fields'] = 'ids';
        return $this;
    }

    /**
     * Deprectated, use idsOnly() instead.
     *
     * @deprecated 1.0.1
     * @return self
     */
    public function ids(): self
    {
        return $this->idsOnly();
    }

    /**
     * Fetch results by a list of post IDs.
     *
     * @param array $ids A list of post IDs to include.
     * @param boolean $ignoreStickyPosts Optional
     * @return self
     */
    public function include(array $ids, $parent = false): self
    {
        if (count($ids) < 1) {
            return $this;
        }

        $clean = array_filter($ids, 'is_numeric');

        if (count($clean) < 1) {
            return $this;
        }

        $key = $parent ? 'post_parent__in' : 'post__in';
        $this->queryParams[$key] = $clean;
        $this->limit(-1);

        return $this;
    }

    /**
     * Set `ignore_sticky_posts` to true.
     *
     * @return self
     */
    public function ignoreStickyPosts(): self
    {
        $this->queryParams['ignore_sticky_posts'] = true;
        return $this;
    }

    /**
     * Fetch the latest entries by `post_date`.
     *
     * @param int $limit Option, default is `posts_per_page` setting value.
     * @param bool $ignoreStickyPosts Optional, default is false.
     * @return self
     */
    public function latest($limit = 0, $ignoreStickyPosts = false): self
    {
        $_limit = empty($limit) ? (int)get_option('posts_per_page') : (int)$limit;
        $this->orderBy('post_date')->limit($_limit);

        if ($ignoreStickyPosts) {
            $this->ignoreStickyPosts();
        }

        return $this;
    }

    /**
     * Set the `posts_per_page` field.
     *
     * @param int $limit
     * @return self
     */
    public function limit($limit): self
    {
        $this->queryParams['posts_per_page'] = (int)$limit;
        return $this;
    }

    /**
     * Sets cache expires to indefinite. Same as cache(0).
     *
     * @return self
     */
    public function nocache(): self
    {
        return $this->cache(0);
    }

    /**
     * Sets the `order` parameter.
     *
     * @param string $order
     * @return self
     */
    public function order($order = 'DESC'): self
    {
        $_order = strtoupper($order);

        if (in_array($_order, ['DESC', 'ASC'])) {
            $this->queryParams['order'] = $_order;
        }

        return $this;
    }

    /**
     * Sets the `orderby` value.
     *
     * @param string $by
     * @return self
     */
    public function orderBy($by): self
    {
        if (!empty($by)) {
            $this->queryParams['orderby'] = $by;
        }

        return $this;
    }

    /**
     * Sets the `paged` parameter and sets `no_found_rows` to false.
     *
     * @param int $num
     * @return self
     */
    public function page($num): self
    {
        if ((int)$num > -1) {
            $this->queryParams['paged'] = (int)$num;
            $this->queryParams['no_found_rows'] = false;
        }

        return $this;
    }

    /**
     * Get the post type mapping value. Defaults to 'post' if mapping not found.
     *
     * @param string $postClass
     * @return string
     */
    private function getPostTypeValue($postClass): string
    {
        $postClasses = apply_filters('Timber\PostClassMap', []);

        if (is_array($postClasses)) {
            $table = array_flip($postClasses);

            if (!empty($table[$postClass])) {
                return $table[$postClass];
            }
        }

        return 'post';
    }
}
