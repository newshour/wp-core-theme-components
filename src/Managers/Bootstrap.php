<?php

/**
 * @version 1.0.0
 */

namespace NewsHour\WPCoreThemeComponents\Managers;

use Twig\Environment;
use Twig\TwigFunction;

use NewsHour\WPCoreThemeComponents\Utilities;

/**
 * Bootstraps additional Wordpress settings and functionality needed by
 * the library.
 */
class Bootstrap implements WordpressManager {

    /**
     * @return string
     */
    public function __toString(): string {

        return self::class;

    }

    /**
     * @return void
     */
    public function run(): void {

        add_filter('admin_init', [$this, 'extendGeneralSettingsPage' ]);
        add_filter('init', [$this, 'addTwigFunctions']);

    }

    /**
     * Extends the "general" settings page.
     *
     * @return void
     */
    public function extendGeneralSettingsPage(): void {

        // Default schema.org organization logo URL.
        add_settings_field(
            'core_theme_org_logo_url-id',
            'Organization Logo (URL)',
            function() {
                echo sprintf(
                    '<input name="core_theme_org_logo_url" type="text" id="core_theme_org_logo_url-id" value="%s" class="regular-text">',
                    get_option('core_theme_org_logo_url', '')
                );
            },
            'general',
            'default',
            ['label_for' => 'core_theme_org_logo_url-id']
        );

        register_setting(
            'general',
            'core_theme_org_logo_url',
            [
                'description' => 'Organization logo image URL.',
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw'
            ]
        );

        // Default social media thumbnail URL.
        add_settings_field(
            'core_theme_social_img_url-id',
            'Default Social Image (URL)',
            function() {
                echo sprintf(
                    '<input name="core_theme_social_img_url" type="text" id="core_theme_social_img_url-id" value="%s" class="regular-text">',
                    get_option('core_theme_social_img_url', '')
                );
            },
            'general',
            'default',
            ['label_for' => 'core_theme_social_img_url-id']
        );

        register_setting(
            'general',
            'core_theme_social_img_url',
            [
                'description' => 'Default social image URL.',
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw'
            ]
        );

        // Default Facebook URL.
        add_settings_field(
            'core_theme_facebook_page_url-id',
            'Facebook Page (URL)',
            function() {
                echo sprintf(
                    '<input name="core_theme_facebook_page_url" type="text" id="core_theme_facebook_page_url-id" value="%s" class="regular-text">',
                    get_option('core_theme_facebook_page_url', '')
                );
            },
            'general',
            'default',
            ['label_for' => 'core_theme_facebook_page_url-id']
        );

        register_setting(
            'general',
            'core_theme_facebook_page_url',
            [
                'description' => 'Default Facebook page.',
                'type' => 'string',
                'sanitize_callback' => 'esc_url_raw'
            ]
        );

        // Default Facebook App Id(s) - can be CSV string.
        add_settings_field(
            'core_theme_facebook_app_id-id',
            'Facebook App Id(s)',
            function() {
                echo sprintf(
                    '<input name="core_theme_facebook_app_id" type="text" id="core_theme_facebook_app_id-id" value="%s" class="regular-text">',
                    get_option('core_theme_facebook_app_id', '')
                );
            },
            'general',
            'default',
            ['label_for' => 'core_theme_facebook_app_id-id']
        );

        register_setting(
            'general',
            'core_theme_facebook_app_id',
            [
                'description' => 'Default Facebook App ID(s).',
                'type' => 'string',
                'sanitize_callback' => fn ($value) => preg_replace('/\s+/', ' ', sanitize_text_field($value))
            ]
        );

        // Default Facebook Page Id(s) - can be CSV string.
        add_settings_field(
            'core_theme_facebook_page_id-id',
            'Facebook Page Id(s)',
            function() {
                echo sprintf(
                    '<input name="core_theme_facebook_page_id" type="text" id="core_theme_facebook_page_id-id" value="%s" class="regular-text">',
                    get_option('core_theme_facebook_page_id', '')
                );
            },
            'general',
            'default',
            ['label_for' => 'core_theme_facebook_page_id-id']
        );

        register_setting(
            'general',
            'core_theme_facebook_page_id',
            [
                'description' => 'Default Facebook Page ID(s).',
                'type' => 'string',
                'sanitize_callback' => fn ($value) => preg_replace('/\s+/', ' ', sanitize_text_field($value))
            ]
        );

        // Default Twitter handle.
        add_settings_field(
            'core_theme_twitter_handle-id',
            'Twitter Handle (@)',
            function() {
                echo sprintf(
                    '<input name="core_theme_twitter_handle" type="text" id="core_theme_twitter_handle-id" value="%s" class="regular-text">',
                    get_option('core_theme_twitter_handle', '')
                );
            },
            'general',
            'default',
            ['label_for' => 'core_theme_twitter_handle-id']
        );

        register_setting(
            'general',
            'core_theme_twitter_handle',
            [
                'description' => 'Default Twitter handle.',
                'type' => 'string',
                'sanitize_callback' => fn ($value) => str_replace('@', '', sanitize_text_field($value))
            ]
        );

        // Default Twitter card image option.
        add_settings_field(
            'core_theme_twitter_large_image-id',
            'Use large Twitter images',
            function() {

                $checked = (int) get_option('core_theme_twitter_large_image', 0) == 1 ? ' checked="checked"' : '';

                echo sprintf(
                    '<input name="core_theme_twitter_large_image" type="checkbox" id="core_theme_twitter_large_image-id" value="1" class="regular-text"%s>',
                    $checked
                );

                echo '<p class="date-time-doc"><a href="https://developer.twitter.com/en/docs/twitter-for-websites/cards/overview/summary-card-with-large-image" target="_blank" rel="noopener noreferrer">Documentation on Twitter Cards with Large Images</a>.</p>';

            },
            'general',
            'default',
            ['label_for' => 'core_theme_twitter_large_image-id']
        );

        register_setting(
            'general',
            'core_theme_twitter_large_image',
            [
                'description' => 'Use large images in Twitter cards?',
                'type' => 'string',
                'sanitize_callback' => fn ($value) => is_numeric($value) ? absint($value) : 0,
                'default' => 0
            ]
        );

        // Default Twitter Do Not Track option.
        add_settings_field(
            'core_theme_twitter_do_not_track-id',
            "Use Twitter's DNT Privacy tag",
            function() {

                $checked = (int) get_option('core_theme_twitter_do_not_track', 0) == 1 ? ' checked="checked"' : '';

                echo sprintf(
                    '<input name="core_theme_twitter_do_not_track" type="checkbox" id="core_theme_twitter_do_not_track-id" value="1" class="regular-text"%s>',
                    $checked
                );

                echo '<p class="date-time-doc"><a href="https://developer.twitter.com/en/docs/twitter-for-websites/privacy" target="_blank" rel="noopener noreferrer">Documentation on Twitter privacy options</a>.</p>';

            },
            'general',
            'default',
            ['label_for' => 'core_theme_twitter_do_not_track-id']
        );

        register_setting(
            'general',
            'core_theme_twitter_do_not_track',
            [
                'description' => "Use Twitter's DNT Privacy meta tag?",
                'type' => 'string',
                'sanitize_callback' => fn ($value) => is_numeric($value) ? absint($value) : 0,
                'default' => 0
            ]
        );

    }

    /**
     * Adds common helper functions for access in Twig templates.
     *
     * @return void
     */
    public function addTwigFunctions(): void {

        // Add static_url function.
        add_filter('timber/twig', function(Environment $twig) {
            $twig->addFunction(new TwigFunction('static_url', fn ($path) => Utilities::staticUrl($path)));
            return $twig;
        });

        // Add home_url function.
        add_filter('timber/twig', function(Environment $twig) {
            $twig->addFunction(new TwigFunction('home_url', 'home_url'));
            return $twig;
        });

    }

}