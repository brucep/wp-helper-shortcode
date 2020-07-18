<?php

namespace Brucep\WordPress\ShortcodeHelper;

final class QuicktagHelper
{
    private static array $quicktags = [];
    private static array $defaultPostTypes = [];

    public static function add(
        string $label,
        string $open,
        ?string $close = null,
        ?array $postTypes = null): void
    {
        $postTypes ??= self::$defaultPostTypes;

        self::$quicktags[] = [$label, $open, $close, $postTypes];
    }

    public static function addForShortcode(
        string $tag,
        ?string $label = null,
        bool $selfClosing = false,
        string $atts = '',
        $postTypes = null): void
    {
        $postTypes ??= self::$defaultPostTypes;

        if ($atts) {
            $atts = ' '.$atts;
        }

        if ($selfClosing) {
            $open = sprintf('[%s%s /]', $tag, $atts);
            $close = null;
        } else {
            $open = sprintf('[%s%s]', $tag, $atts);
            $close = sprintf('[/%s]', $tag);
        }

        self::$quicktags[] = [$label ?? $tag, $open, $close, $postTypes];
    }

    public static function enqueue(): void
    {
        $buttons = [];

        foreach (self::$quicktags as &$qt) {
            $button = sprintf(
                'QTags.addButton("bpwp_quicktag_%s", "%s", "%s"%s);',
                md5($qt[0]),
                self::escape($qt[0]),
                self::escape($qt[1]),
                $qt[2] ? sprintf(', "%s"', self::escape($qt[2])) : ''
            );

            $buttons[$button] = $qt[3];
        }

        add_action('admin_enqueue_scripts', function ($hook) use ($buttons) {
            if (!in_array($hook, ['post.php', 'post-new.php'])) {
                return;
            }

            if ('post-new.php' === $hook) {
                $postType = $_GET['post_type'] ?? null;
            } else {
                $postType = get_post_type() ?: null;
            }

            $buttons = array_filter(
                $buttons,
                fn ($b) => [] === $b || in_array($postType, $b)
            );

            if (empty($buttons)) {
                return;
            }

            wp_enqueue_script('quicktags');

            $script = implode("\n", array_keys($buttons));

            wp_register_script('bpwp_quicktag', false);
            wp_enqueue_script('bpwp_quicktag', false, ['quicktags'], null, true);
            wp_add_inline_script('bpwp_quicktag', $script);
        });

        self::$quicktags = [];
    }

    private static function escape(string $text): string
    {
        $text = str_replace("\r", '', $text);
        $text = str_replace("\n", '\\n', addslashes($text));

        return $text;
    }

    public static function getDefaultPostTypes(): array
    {
        return self::$defaultPostTypes;
    }

    public static function setDefaultPostTypes(array $postTypes): void
    {
        self::$defaultPostTypes = $postTypes;
    }

    private function __construct()
    {
    }
}
