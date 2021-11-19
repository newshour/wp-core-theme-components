<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents;

use Exception;

use Carbon\Carbon;

use Timber\Image;
use Timber\TextHelper;

use NewsHour\WPCoreThemeComponents\Http\Factories\PackageFactory;

/**
 * Utility methods.
 *
 * @final
 */
final class Utilities {

    /**
     * @param string $name
     * @param string $value
     * @return string
     */
    public static function createMetaTag($name, $value): string {

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
    public static function createMetaPropertyTag($name, $value): string {

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
    public static function createLdJsonTag(array $src): string {

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
    public static function createLinkTag($rel, $url, $type = '', $title = ''): string {

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
     * Splits a string by a token. Default token is a space. Essentially a wrapper
     * for explode() to catch errors.
     *
     * @param string $str
     * @param string $token Optional
     * @return array
     */
    public static function splitter($str, $token = ' '): array {

        if (empty($str) || !is_string($str)) {
            return [];
        }

        try {

            $xstr = explode($token, $str);

            if ($xstr !== false) {
                trigger_error(
                    'String could not be split using the supplied token.',
                    E_USER_WARNING
                );
            }

        // PHP 8 will throw ValueError.
        } catch (Exception $e) {

            trigger_error(
                $e->getMessage(),
                E_USER_WARNING
            );

        }

        return $xstr;

    }

    /**
     * @param Image $image
     * @param string $size
     * @param string $dim
     * @return int
     */
    public static function getImageDimension(Image $image, $size, $dim): int {

        $sizes = $image->sizes;

        if (isset($sizes[$size][$dim])) {
            return (int)$sizes[$size][$dim];
        }

        return 0;

    }

    /**
     * Performs a case-insensitive key check.
     *
     * @param mixed $key
     * @param array $array
     * @return boolean
     */
    public static function hasKey($key, array $array): bool {

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
     * Builds a static assets URL. The constant ASSETS_URL should be set to generate
     * absolute URLs.
     *
     * @param string $path The relative path of the asset.
     * @return string
     */
    public static function static_url($path) {

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
    public static function toCarbonObj($value, $timezone = null): Carbon {

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

}
