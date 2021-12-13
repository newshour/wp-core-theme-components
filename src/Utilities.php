<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents;

use Exception;
use InvalidArgumentException;
use WP_Http;
use Carbon\Carbon;
use Timber\Image;
use Timber\TextHelper;
use NewsHour\WPCoreThemeComponents\Http\Factories\PackageFactory;

/**
 * Common utility methods.
 *
 * @final
 */
final class Utilities
{
    /**
     * Returns a unique array of strings. If $removeEmpty is set, the function will remove any empty values
     * by applying array_filter().
     *
     * @param array $strings
     * @param boolean $removeEmpty Optional, default is false.
     * @return string[]
     */
    public static function stringArrayUnique(array $strings, $removeEmpty = false): array
    {
        if (count($strings) < 1) {
            return [];
        }

        $filtered = array_map(
            fn ($item) => is_string($item) || is_numeric($item) ? trim((string) $item) : '',
            $strings
        );

        if ($removeEmpty) {
            $filtered = array_filter($filtered);
        }

        return array_unique($filtered);
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function createMetaTag($name, $value): string
    {
        if (empty($name) || empty($value)) {
            return '';
        }

        return sprintf('<meta name="%s" content="%s" />', $name, esc_html(trim($value)));
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function createMetaPropertyTag($name, $value): string
    {
        if (empty($name) || empty($value)) {
            return '';
        }

        return sprintf('<meta property="%s" content="%s" />', $name, esc_html(trim($value)));
    }

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function createLdJsonTag(array $src): string
    {
        if (empty($src)) {
            return '';
        }

        return sprintf('<script type="application/ld+json">%s</script>', json_encode($src));
    }

    /**
     * @param string $rel
     * @param string $url
     * @param string $type
     * @param string $title
     * @return string
     */
    public static function createLinkTag($rel, $url, $type = '', $title = ''): string
    {
        if (empty($rel) || empty($url)) {
            return '';
        }

        $attr = [
            'rel' => esc_attr($rel),
            'href' => esc_url(trim($url))
        ];

        if (!empty($type)) {
            $attr['type'] = esc_attr($type);
        }

        if (!empty($title)) {
            $attr['title'] = esc_attr($title);
        }

        $attrHtml = [];

        foreach ($attr as $k => $v) {
            $attrHtml[] = sprintf('%s="%s"', $k, $v);
        }

        return '<link ' . implode(' ', $attrHtml) . ' />';
    }

    /**
     * Retrieve the image size dimension. For example, to retrieve the height dimension of a "large"
     * Wordpress/Timber image:
     *
     * ```
     * getImageDimension($image, 'large', 'height');
     * ```
     *
     * @param Image $image
     * @param string $size
     * @param string $dim
     * @return integer
     */
    public static function getImageDimension(Image $image, $size, $dim): int
    {
        $sizes = $image->sizes;

        if (isset($sizes[$size][$dim])) {
            return (int)$sizes[$size][$dim];
        }

        return 0;
    }

    /**
     * Fetch a remote image's size dimensions.
     *
     * @param string $url
     * @param array $allowed Optional, allowed image types. e.g. png, gif, jpg...
     * @throws InvalidArgumentException
     * @return array
     */
    public static function getRemoteImageDim($url, array $allowed = []): array
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('The URL passed is not a valid URL.');
        }

        if (!self::validateUrlExtension($url, $allowed)) {
            $types = array_map(fn ($item) => strtolower(ltrim($item, '.')), $allowed);
            throw new InvalidArgumentException(
                sprintf(
                    'The image is not one of these types: %s',
                    implode(', ', $types)
                )
            );
        }

        // Make sure image exists.
        $resp = (new WP_http())->head($url);

        if (!is_wp_error($resp)) {
            // Now fetch remote image size. This can be slow...
            list($width, $height, $type, $attr) = @getimagesize($url);
            return ['width' => (int) $width, 'height' => (int) $height];
        }

        return ['width' => 0, 'height' => 0];
    }

    /**
     * Performs a case-insensitive key check.
     *
     * @param mixed $key
     * @param array $array
     * @return boolean
     */
    public static function hasKey($key, array $array): bool
    {
        // Do some quick checks first.
        if (count($array) < 1) {
            return false;
        }

        if (array_key_exists($key, $array)) {
            return true;
        }

        // ...now look for the key.
        $generator = fn ($array) => (yield from $array);

        foreach ($generator($array) as $k => $v) {
            if (is_string($k) && strcasecmp($k, $key) == 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a string by a token. Default token is a space. Essentially a wrapper
     * for explode() to catch errors.
     *
     * @param string $str
     * @param string $token Optional
     * @return string[]
     */
    public static function splitter($str, $token = ' '): array
    {
        if (empty($str) || !is_string($str)) {
            return [];
        }

        try {
            if (($xstr = explode($token, $str)) !== false) {
                return $xstr;
            }
        } catch (Exception $e) {
            // PHP 8 will throw ValueError.
        }

        trigger_error(
            'String could not be split using the supplied token.',
            E_USER_WARNING
        );

        return [];
    }

    /**
     * Builds a static assets URL. The constant ASSETS_URL should be set to generate
     * absolute URLs.
     *
     * @param string $path The relative path of the asset.
     * @return string
     */
    public static function staticUrl($path)
    {
        if (defined('ASSETS_URL')) {
            $_path = TextHelper::starts_with($path, '/') ? $path : '/' . $path;
            return rtrim(trim(ASSETS_URL), '/') . PackageFactory::get()->getUrl($_path);
        }

        return PackageFactory::get()->getUrl($path);
    }

    /**
     * Converts a mixed value (int|string) into a Carbon date object. An optional
     * timezone can be passed. If no timezone is passed, the value of wp_timezone()
     * is assumed.
     *
     * @param mixed $value
     * @param string $timezone Optional, default is wp_timezone().
     * @return Carbon
     */
    public static function toCarbonObj($value, $timezone = null): Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (empty($timezone)) {
            $timezone = wp_timezone();
        }

        if (empty($value)) {
            return Carbon::now()->setTimezone($timezone);
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value, $timezone);
        }

        return Carbon::createFromTimestamp(strtotime($value), $timezone);
    }

    /**
     * Validates that a URL string uses an allowed extension.
     *
     * @param string $url
     * @param array $allowed
     * @return boolean
     */
    public static function validateUrlExtension($url, array $allowed): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL) || count($allowed) < 1) {
            return false;
        }

        $path = strtolower(parse_url($url, PHP_URL_PATH));
        $types = array_map(fn ($item) => strtolower(ltrim($item, '.')), $allowed);
        $found = array_filter($types, fn ($val) => TextHelper::ends_with($path, '.' . $val));

        return empty($found) ? false : true;
    }
}
