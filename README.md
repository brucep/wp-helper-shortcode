# wp-helper-shortcode

These classes help WordPress developers provide
[shortcodes](https://codex.wordpress.org/Shortcode_API) and
[quicktags](https://codex.wordpress.org/Quicktags_API).

## Examples

Shortcodes:

```php
use Brucep\WordPress\ShortcodeHelper\ShortcodeHelper;

ShortcodeHelper::add('example', function ($atts, $content = null, $tag = null) {
    // shortcode functionality
});
```

Quicktags:

```php
use Brucep\WordPress\ShortcodeHelper\QuicktagHelper;

QuicktagHelper::add('Example', '[example]', '[/example]');
QuicktagHelper::add('HTML Comment', '<!--', '-->');
QuicktagHelper::enqueue(); // Quicktags are enqueued as a single script
```
