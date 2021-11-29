<?php

/**
 * Mock Wordpress functions for unit testing.
 */

/**
 * @param string $filterName
 * @param mixed $value
 * @return mixed
 */
function apply_filters($filterName, $value)
{
    return $value;
}

/**
 * @param string $format
 * @param boolean $timestamp_with_offset
 * @param boolean $gmt
 * @return string
 */
function date_i18n($format, $timestamp_with_offset = false, $gmt = false)
{
    if (empty($format)) {
        return date('Y-m-d H:i:s');
    }

    return date($format);
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
 * @param integer $post
 * @param boolean $leavename
 * @return string
 */
function get_permalink($post = 0, $leavename = false)
{
    return 'http://some/url.localhost';
}

/**
 * @param mixed $post
 * @param string $output
 * @param string $filter
 * @return mixed
 */
function get_post($post = null, $output = '', $filter = 'raw')
{
    if (empty($output) || $output == 'OBJECT') {
        return new stdClass();
    }

    return [];
}

/**
 * @param string $format
 * @param boolean $gmt
 * @param mixed $post
 * @param boolean $translate
 * @return string
 */
function get_post_modified_time($format = 'U', $gmt = false, $post = null, $translate = false)
{
    return date($format);
}

/**
 * @param mixed $post
 * @return int
 */
function get_post_thumbnail_id($post = null)
{
    return 0;
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
