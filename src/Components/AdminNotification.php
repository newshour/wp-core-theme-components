<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components;

/**
 * Display Wordpress admin notification banners. This class consists of mostly static helper methods
 * to store notification data as COOKIE values (since Wordpress does a number of internal redirects,
 * we need to store data between requests). In order to display the notification, `flashMessage()`
 * must be called at some point.
 */
class AdminNotification
{
    public const COOKIE_KEY = 'flash_message';
    public const MESSAGE_TYPE_ERROR = 'error';
    public const MESSAGE_TYPE_INFO = 'info';
    public const MESSAGE_TYPE_WARNING = 'warning';

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    public static function message(string $message = '', string $type = AdminNotification::MESSAGE_TYPE_INFO): void
    {
        $types = [
            self::MESSAGE_TYPE_ERROR,
            self::MESSAGE_TYPE_INFO,
            self::MESSAGE_TYPE_WARNING
        ];

        if (!in_array($type, $types)) {
            return;
        }

        $parsed = parse_url(WP_HOME, PHP_URL_PATH);
        $path = empty($parsed) ? '/' : '/' . ltrim($parsed);

        setcookie(
            self::COOKIE_KEY,
            http_build_query(['type' => $type, 'message' => $message]),
            time() + 10,
            $path,
            '',
            WP_ENV == 'development' ? false : true,
            true
        );
    }

    /**
     * @param string $message
     * @return void
     */
    public static function error(string $message): void
    {
        self::message($message, self::MESSAGE_TYPE_ERROR);
    }

    /**
     * @param string $message
     * @return void
     */
    public static function info(string $message): void
    {
        self::message($message, self::MESSAGE_TYPE_INFO);
    }

    /**
     * @param string $message
     * @return void
     */
    public static function warning($message): void
    {
        self::message($message, self::MESSAGE_TYPE_WARNING);
    }

    /**
     * @return void
     */
    public static function flashMessage(): void
    {
        if (empty($_COOKIE[self::COOKIE_KEY])) {
            return;
        }

        $messageVars = [];
        parse_str($_COOKIE[self::COOKIE_KEY], $messageVars);

        $type = $messageVars['type'] ?? self::MESSAGE_TYPE_INFO;
        $message = $messageVars['message'] ?? '';

        add_action('admin_notices', function () use ($type, $message) {
            echo sprintf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                strtolower($type),
                $message
            );
        });

        $parsed = parse_url(WP_HOME, PHP_URL_PATH);
        $path = empty($parsed) ? '/' : '/' . ltrim($parsed);

        unset($_COOKIE[self::COOKIE_KEY]);
        setcookie(self::COOKIE_KEY, null, -1, $path);
    }
}
