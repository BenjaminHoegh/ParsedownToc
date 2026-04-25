<p align="center">
  <a href="https://github.com/BenjaminHoegh/ParsedownToc">
    <img alt="ParsedownToc" src="https://github.com/BenjaminHoegh/ParsedownToc/blob/master/.github/parsedownToc.png" height="330" />
  </a>
</p>

# ParsedownToc

![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/ParsedownToc.svg?style=flat-square)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownToc.svg?style=flat-square)

**ParsedownToc** is a table-of-contents extension for Parsedown and ParsedownExtra. It generates heading ids, collects headings while parsing, and can replace a `[toc]` tag with a rendered table of contents.

It is based on [@KEINOS toc extention](https://github.com/KEINOS/parsedown-extension_table-of-contents).

## Features:

- **Automatic ToC generation:** Replaces `[toc]` with a generated nested list of headings.
- **Configurable anchor generation:** Supports custom slug delimiters, replacements, transliteration, URL encoding, reserved ids, and custom callbacks.
- **Custom heading ids:** Respects explicit heading ids and avoids reusing them for generated anchors.
- **ParsedownExtra support:** Works with Parsedown 1.8 and ParsedownExtra 0.9.

## Requirements:

- PHP 7.4 or later.
- Requires Parsedown 1.8.x.
- Requires ParsedownExtra 0.9.x.

## Installation:

Ensure you have Composer installed on your system.

1. Install the ParsedownToc package using Composer:

   ```bash
   composer require benjaminhoegh/ParsedownToc
   ```

2. Alternatively, you can download the [latest release](https://github.com/BenjaminHoegh/ParsedownToc/releases/latest) and include `Parsedown.php` in your project.

## Quick Start:

### Replace `[toc]` inside the document

```php
<?php
require 'vendor/autoload.php';

$content = file_get_contents('sample.md');
$ParsedownToc = new ParsedownToc();

$html = $ParsedownToc->text($content);

echo $html;
```

### Render the body and ToC separately

```php
<?php
$content = file_get_contents('sample.md');
$ParsedownToc = new \ParsedownToc();

$body = $ParsedownToc->body($content);
$toc  = $ParsedownToc->contentsList();

echo $toc;  // ToC in <ul> list
echo $body; // Main content
```

### Configure the instance

You can configure ParsedownToc either by passing an options array to `setOptions()`, or by calling the individual setter methods — both approaches are equivalent and can be mixed freely.

**Using `setOptions()` (bulk):**

```php
<?php
$ParsedownToc = new ParsedownToc();

$ParsedownToc->setOptions([
    'heading_levels' => ['h2', 'h3'],
    'toc_items_limit' => 10,
    'slug_delimiter' => '_',
    'prefix' => '/docs/page',
    'reserved_ids' => ['introduction'],
]);
```

**Using individual setters (granular):**

```php
<?php
$ParsedownToc = new ParsedownToc();

$ParsedownToc->setHeadingLevels(['h2', 'h3']);
$ParsedownToc->setTocItemsLimit(10);
$ParsedownToc->setSlugDelimiter('_');
$ParsedownToc->setTocPrefix('/docs/page');
$ParsedownToc->setReservedIds(['introduction']);
```

### Use a custom slug callback

```php
<?php
$ParsedownToc->setCreateAnchorIDCallback(function ($text, $options) {
    $slugify = new Slugify();

    return $slugify->slugify($text);
});
```

The callback result still goes through the package's uniqueness checks, so duplicate ids are avoided automatically.

## Configuration:

There are two interchangeable ways to configure ParsedownToc:

- **`setOptions(array $options)`** — pass multiple options at once as an associative array. Only the keys you specify are changed; all others keep their defaults.
- **Individual setters** — call a dedicated method for each option (e.g. `setSlugDelimiter('-')`). Useful when you only need to change one or two values.

Both approaches produce the same result and can be mixed freely.

Below are all available options with their defaults:

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| `heading_levels` | `array` | `['h1', 'h2', 'h3', 'h4', 'h5', 'h6']` | Which heading levels are included in the ToC. |
| `slug_delimiter` | `string` | `-` | Delimiter used when sanitizing generated anchor ids. |
| `toc_items_limit` | `int|null` | `null` | Maximum number of headings to include in the generated ToC. |
| `slug_lowercase` | `bool` | `true` | Lowercase generated anchor ids before sanitizing them. |
| `slug_replacements` | `array|null` | `null` | Plain-string or regex replacements applied before slugging. |
| `slug_transliterate` | `bool` | `false` | Transliterate supported characters before slug sanitization. |
| `slug_urlencode` | `bool` | `false` | Use PHP's built-in `urlencode` for generated ids and skip the normal slug pipeline. |
| `reserved_ids` | `array` | `[]` | Anchor ids that must not be emitted by automatic generation. |
| `prefix` | `string` | `''` | Prefix generated ToC links with a base path or URL. |
| `toc_tag` | `string` | `[toc]` | Markdown tag that will be replaced by the generated ToC. |
| `toc_id` | `string` | `toc` | `id` attribute used on the wrapper element around the generated ToC. |

### Methods:

**Parsing:**

- `text(string $text): string`
  Parse a markdown document and replace the configured ToC tag if present.
- `body(string $text): string`
  Parse a markdown document without replacing the ToC tag.
- `contentsList(string $type_return = 'html')`
  Return the ToC for the last parsed document as `html`, `json`, or `array`.

**Configuration setters** (each corresponds to an option key in `setOptions()`):

- `setOptions(array $options): void`
  Set multiple options at once. Merges with current options; unspecified keys keep their values.
- `setHeadingLevels(array $headingLevels): void`
  Set which heading levels are included in the ToC.
- `setSlugDelimiter(string $delimiter): void`
  Set the delimiter used for generated anchor ids.
- `setTocItemsLimit(?int $limit): void`
  Limit the number of headings included in the ToC.
- `setSlugLowercase(bool $lowercase): void`
  Control whether generated anchor ids are lowercased.
- `setSlugReplacements(?array $replacements): void`
  Apply plain-string or regex replacements before slug generation.
- `setSlugTransliterate(bool $transliterate): void`
  Transliterate supported characters before slug generation.
- `setSlugUrlencode(bool $urlencode): void`
  Use `urlencode()` for generated ids instead of the normal slug pipeline.
- `setReservedIds(array $reservedIds): void`
  Reserve ids so generated anchors will not reuse them.
- `setTocPrefix(string $prefix): void`
  Prefix generated ToC links.
- `setTocTag(string $tag): void`
  Change the markdown tag used for ToC replacement.
- `setTocId(string $id): void`
  Change the wrapper id used for the rendered ToC.
- `setCreateAnchorIDCallback(callable $callback): void`
  Provide a custom callback for anchor generation.

## Notes:

- `contentsList()` returns data for the most recently parsed document.
- `body()` and `text()` reset parser state before each new document parse.
- Explicit heading ids are preserved and reserved from later automatic id generation.