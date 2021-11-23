<?php

/**
 * Mock Wordpress functions for unit testing.
 */

/**
 * @param string $filterName
 * @param mixed $args
 * @return void
 */
function apply_filters($filterName, $args)
{
    return $args;
}

/**
 * @param string $src
 * @return void
 */
function esc_html($src)
{
    return htmlentities($src, ENT_QUOTES, 'utf-8');
}

/**
 * @param string $key
 * @return void
 */
function get_bloginfo($key)
{
    switch ($key) {
        case 'name':
            return 'Test Name';

        case 'description':
            return 'Test Description';
    }

    return '';
}

/**
 * @return string
 */
function get_locale()
{
    return 'en_US';
}

/**
 * @param string $key
 * @param string $default
 * @return mixed
 */
function get_option($key, $default = '')
{
    return $default;
}

/**
 * @return boolean
 */
function is_user_logged_in()
{
    return false;
}

/**
 * @return string
 */
function wp_timezone()
{
    return 'UTC';
}
