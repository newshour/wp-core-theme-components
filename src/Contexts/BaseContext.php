<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Contexts;

use ArrayAccess;
use Countable;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a base context object and calls Timber::context(). All Context
 * implementations should extend from this base Context.
 */
class BaseContext implements ArrayAccess, Context, Countable
{
    // Context data dictionary.
    private array $data = [];

    // Symfony request object.
    private Request $request;

    /**
     * Pass optional keyword args.
     *
     * @param array $initial - Add initial key value pairs.
     */
    public function __construct(Request $request, array $initial = [])
    {
        $this->request = $request;
        $this->data = $initial;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s [vars: %s]',
            self::class,
            implode('|', array_keys($this->data))
        );
    }

    /**
     * Returns the context dictionary.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param  mixed $offset
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->data[] = $value;
            return;
        }

        $this->data[$offset] = $value;
    }

    /**
     * @param  mixed $offset
     * @return boolean
     */
    public function offsetExists($offset): bool
    {
        return isset($this->data[$offset]);
    }

    /**
     * @param  mixed $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @param  mixed $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }

    /**
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function set($key, $value = ''): void
    {
        $this->offsetSet($key, $value);
    }
}
