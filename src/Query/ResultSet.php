<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Query;

use Countable;

/**
 * ResultSets provide a fluent interface to fetching data.
 */
interface ResultSet extends Countable
{
    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * Creates a ResultSet instance.
     *
     * @param string $postClass
     * @param array $params
     * @return ResultSet
     */
    public static function factory($postClass = '', array $params = []): ResultSet;

    /**
     * Add the results directly to a collection.
     *
     * @param \Doctrine\Common\Collections\Collection $collection
     * @param mixed $key
     * @return void
     */
    public function addToCollection(\Doctrine\Common\Collections\Collection &$collection, $key = null): void;

    /**
     * Sets the cache expires time in seconds. -1 is no cache, 0 is
     * cache forever.
     *
     * @param int $seconds
     * @return ResultSet
     */
    public function cache($seconds): ResultSet;

    /**
     * Returns the result set array based on the set query params.
     * This method will hit the database.
     *
     * @return array
     */
    public function get(): array;

    /**
     * Retrieve the first result by primary key.
     *
     * @param int $pk
     * @return object|null
     */
    public function pk($pk): ?object;

    /**
     * Returns true if record exists.
     *
     * @param int $pk
     * @return boolean
     */
    public function exists($pk): bool;

    /**
     * Filter a query by keyword args. See WP_Query documentation for a full
     * list of args.
     *
     * @param array $params
     * @return ResultSet
     */
    public function filter(array $params): ResultSet;

    /**
     * Retrieve only the first result. This method will hit the database.
     *
     * @return mixed
     */
    public function first(): mixed;

    /**
     * Returns a slice of the collection starting at the given index.
     * Similar to Laravel's slice(). This method will hit the database.
     *
     * @param int $start
     * @return array
     */
    public function slice($start): array;

    /**
     * Shuffles (and slices) the result set. This method will hit the database.
     *
     * @param integer $andSlice - optional
     * @return array
     */
    public function shuffle($andSlice = 0): array;

    /**
     * Set ordering to ASC.
     *
     * @return ResultSet
     */
    public function asc(): ResultSet;

    /**
     * Set ordering to DESC.
     *
     * @return ResultSet
     */
    public function desc(): ResultSet;

    /**
     * Excludes records by ID. If $parent is true, only parent IDs are
     * excluded.
     *
     * @param array $ids
     * @param boolean $parent
     * @return ResultSet
     */
    public function exclude(array $ids, $parent = false): ResultSet;

    /**
     * Includes records by ID.
     *
     * @param array $ids
     * @return ResultSet
     */
    public function include(array $ids): ResultSet;

    /**
     * Ignore any settings for "sticky posts".
     *
     * @return self
     */
    public function ignoreStickyPosts(): ResultSet;

    /**
     * Retrieve the latest records by date.
     *
     * @return ResultSet
     */
    public function latest(): ResultSet;

    /**
     * Sets the `posts_per_page` parameter.
     *
     * @return ResultSet
     */
    public function limit($limit): ResultSet;

    /**
     * Sets the `order` parameter. Default is `DESC`.
     *
     * @param string $order
     * @return ResultSet
     */
    public function order($order = 'DESC'): ResultSet;

    /**
     * Sets the `orderby` value.
     *
     * @param string $by
     * @return ResultSet
     */
    public function orderBy($by): ResultSet;

    /**
     * Sets the `paged` parameter and sets `no_found_rows` to false.
     *
     * @param int $num
     * @return ResultSet
     */
    public function page($num): ResultSet;
}
