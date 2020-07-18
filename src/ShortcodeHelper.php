<?php

namespace Brucep\WordPress\ShortcodeHelper;

final class ShortcodeHelper
{
    public static function add(
        string $name,
        callable $shortcodeCallback,
        ?array $editorButtonArgs = null): void
    {
        $name = strtolower($name);

        add_shortcode($name, $shortcodeCallback);
        add_filter(
            'no_texturize_shortcodes',
            fn ($shortcodes) => array_merge($shortcodes, [$name]),
        );

        if (is_array($editorButtonArgs)) {
            array_unshift($editorButtonArgs, $name);
            QuicktagHelper::addForShortcode(...$editorButtonArgs);
        }
    }

    public static function getPostOfType(string $input, string $type): ?object
    {
        global $wpdb;

        $query =
            <<<EOQ
            SELECT * FROM {$wpdb->prefix}posts
            WHERE post_type = %s AND (`ID` = %d OR post_name = %s OR post_title = %s)
            EOQ
        ;

        $args = [
            $type,
            is_numeric($input) ? $input : null,
            $input,
            $input,
        ];

        $query = $wpdb->prepare($query, $args);
        $count = $wpdb->query($query);

        if (1 < $count) {
            if (defined('WP_DEBUG') and WP_DEBUG) {
                wp_die(sprintf('Found multiple posts in `%s`.', __FUNCTION__));
            } else {
                return null;
            }
        }

        return $wpdb->get_row($query);
    }

    public static function getClosureForPostType(string $type): \Closure
    {
        return function (array $atts, string $content = null) use ($type) {
            $post = self::getPostofType($atts[0] ?? null, $type);

            if (null === $post) {
                return $content;
            }

            if (empty($content)) {
                $content = $post->post_title;
            }

            $content = sprintf(
                '<a href="%s"%s>%s</a>',
                esc_url(get_post_permalink($post)),
                $content !== $post->post_title ?
                    sprintf(' title="%s"', esc_attr($post->post_title)) : '',
                esc_html($content)
            );

            return $content;
        };
    }

    private function __construct()
    {
    }
}
