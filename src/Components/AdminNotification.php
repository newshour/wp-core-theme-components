<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Components;

/**
 * Display Wordpress admin notification banners. This class consists of mostly static helper methods
 * to display and store notification data as COOKIE values (since Wordpress does a number of internal
 * redirects).
 */
class AdminNotification
{
    public const COOKIE_KEY = 'flash_message';
    public const MESSAGE_TYPE_ERROR = 'error';
    public const MESSAGE_TYPE_INFO = 'info';
    public const MESSAGE_TYPE_WARNING = 'warning';

    /**
     * Display an admin notification. If the notification needs to survive a Wordpress redirect, set the
     * $flash value to "true". Available types:
     *
     *  `AdminNotification::MESSAGE_TYPE_ERROR`
     *  `AdminNotification::MESSAGE_TYPE_INFO`
     *  `AdminNotification::MESSAGE_TYPE_WARNING`
     *
     * @param string $message
     * @param string $type
     * @param boolean $flash Default is false.
     * @return void
     */
    public static function message(
        string $message = '',
        string $type = AdminNotification::MESSAGE_TYPE_INFO,
        $flash = false
    ): void {
        $types = [
            self::MESSAGE_TYPE_ERROR,
            self::MESSAGE_TYPE_INFO,
            self::MESSAGE_TYPE_WARNING
        ];

        if (!in_array($type, $types)) {
            return;
        }

        if ($flash) {
            $parsed = parse_url(WP_HOME, PHP_URL_PATH);
            $path = empty($parsed) ? '/wp/wp-admin' : '/' . trim($parsed, '/') . '/wp/wp-admin';

            setcookie(
                self::COOKIE_KEY,
                http_build_query(['type' => $type, 'message' => $message]),
                time() + 10,
                $path,
                '',
                WP_ENV == 'development' ? false : true,
                true
            );
        } else {
            add_action('admin_notices', function () use ($type, $message) {
                echo sprintf(
                    '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                    strtolower($type),
                    $message
                );
            }, 2, 1);
        }
    }

    /**
     * A shortcut for AdminNotification::MESSAGE_TYPE_ERROR notification types.
     *
     * @param string $message
     * @param boolean $flash
     * @return void
     */
    public static function error(string $message, bool $flash = false): void
    {
        self::message($message, self::MESSAGE_TYPE_ERROR, $flash);
    }

    /**
     * A shortcut for AdminNotification::MESSAGE_TYPE_INFO notification types.
     *
     * @param string $message
     * @param boolean $flash
     * @return void
     */
    public static function info(string $message, bool $flash = false): void
    {
        self::message($message, self::MESSAGE_TYPE_INFO, $flash);
    }

    /**
     * A shortcut for AdminNotification::MESSAGE_TYPE_WARNING notification types.
     *
     * @param string $message
     * @param boolean $flash
     * @return void
     */
    public static function warning(string $message, bool $flash = false): void
    {
        self::message($message, self::MESSAGE_TYPE_WARNING, $flash);
    }

    /**
     * Display the stored cookie message.
     *
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
        }, 2, 1);

        $parsed = parse_url(WP_HOME, PHP_URL_PATH);
        $path = empty($parsed) ? '/wp/wp-admin' : '/' . trim($parsed, '/') . '/wp/wp-admin';

        unset($_COOKIE[self::COOKIE_KEY]);
        setcookie(self::COOKIE_KEY, null, -1, $path);
    }
}
