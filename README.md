<p align="center">
  <a href="https://github.com/BenjaminHoegh/ParsedownToc">
    <img alt="ParsedownToc" src="https://github.com/BenjaminHoegh/ParsedownToc/blob/master/.github/parsedownToc.png" height="330" />
  </a>
</p>

# ParsedownToc

![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/ParsedownToc.svg?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/benjaminhoegh/parsedown-toc)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownToc.svg?style=flat-square)

**ParsedownToc** is an extension for Parsedown and ParsedownExtra that introduces advanced features for developers working with Markdown. It is based on [@KEINOS toc extention](https://github.com/KEINOS/parsedown-extension_table-of-contents)


## Features:

- **Speed:** Super-fast processing.
- **Configurability:** Easily customizable for different use-cases.
- **Custom Header IDs:** Full support for custom header ids.

## Prerequisites:

- Requires Parsedown 1.7.4 or later.

## Installation:

Ensure you have Composer installed on your system.

1. Install the ParsedownToc package using Composer:

   ```bash
   composer require benjaminhoegh/ParsedownToc
   ```

2. Alternatively, you can download the [latest release](https://github.com/BenjaminHoegh/ParsedownToc/releases/latest) and include `Parsedown.php` in your project.

## Usage:

### Basic example:

```php
<?php
require 'vendor/autoload.php';  // autoload

$content = file_get_contents('sample.md');  // Sample Markdown with '[toc]' tag
$ParsedownToc = new ParsedownToc();

$html = $ParsedownToc->text($content);  // Parses '[toc]' tag to ToC if exists
echo $html;
```

### Separate body and ToC:

```php
<?php
$content = file_get_contents('sample.md');
$ParsedownToc = new \ParsedownToc();

$body = $ParsedownToc->body($content);
$toc  = $ParsedownToc->contentsList();

echo $toc;  // ToC in <ul> list
echo $body; // Main content
```

## Configuration:

The `ParsedownToc->setOptions(array $options)` method allows you to configure the main class. Below are the available options along with their default values and descriptions:

| Option               | Type     | Default                                  | Description                                                   |
|----------------------|----------|------------------------------------------|---------------------------------------------------------------|
| selectors            | array    | ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']    | HTML heading tags to include in the ToC.                      |
| delimiter            | string   | `-`                                      | Character used between words and duplicate suffixes.          |
| max_anchor_length    | int      | `null`                                   | Maximum character length for generated anchor IDs.            |
| lowercase            | boolean  | `true`                                   | Convert anchor IDs to lowercase.                              |
| replacements         | array    | none                                     | Custom regex replacements applied before sanitization.        |
| transliterate        | boolean  | `true`                                   | Transliterate non-ASCII characters to ASCII equivalents.      |
| urlencode            | boolean  | `false`                                  | Uses PHP built-in `urlencode` and disables all other options. |
| reserved_ids         | array    | `[]`                                     | Anchor IDs that must not be generated; duplicates are suffixed instead. |
| base_url             | string   | ``                                       | Prefixes each ToC anchor link with the specified URL.         |
| anchor_prefix        | string   | ``                                       | Static string prepended to every generated anchor ID.         |
| toc_tag              | string   | `[toc]`                                  | Markdown tag that is replaced with the rendered ToC.          |
| toc_id               | string   | `toc`                                    | `id` attribute of the wrapping `<div>` around the ToC.        |

### Methods:

The ParsedownToc class offers several methods for different functionalities:

- **text(string $text):** Returns the parsed content and `[toc]` tag(s).
- **body(string $text):** Returns the parsed content without the `[toc]` tag.
- **contentsList([string $type_return='html']):** Returns the ToC in HTML, JSON, or as an array.
    - _Optional:_ Specify the return type as `html`, `json`, or `array`.
- **setTocSelectors(array $selectors):** Set which heading levels to include in the ToC.
- **setTocDelimiter(string $delimiter):** Define a custom delimiter.
- **setTocMaxAnchorLength(?int $length):** Set the maximum character length for generated anchor IDs.
- **setTocLowercase(bool $boolean):** Choose whether anchor IDs should be lowercased.
- **setTocReplacements(?array $replacements):** Provide custom regex replacements applied before sanitization.
- **setTocTransliterate(bool $boolean):** Specify if transliterations should be made.
- **setTocUrlencode(bool $boolean):** Decide if you want to use PHP's built-in `urlencode`.
- **setTocReservedIds(array $reservedIds):** Define anchor IDs that must not be generated; duplicates are suffixed instead.
- **setTocBaseUrl(string $url):** Set a URL prefix for all ToC anchor links.
- **setTocAnchorPrefix(string $prefix):** Prepend a static string to every generated anchor ID.
- **setTocTag(string $tag):** Set a custom ToC markdown tag.
- **setTocId(string $id):** Set a custom `id` attribute for the ToC wrapper element.

### Deprecated methods

The following methods still work but emit an `E_USER_DEPRECATED` notice. Replace them with the equivalents above.

| Deprecated | Replacement |
|---|---|
| `setTocLimit(int $limit)` | `setTocMaxAnchorLength(?int $length)` |
| `setTocBlacklist(array $blacklist)` | `setTocReservedIds(array $reservedIds)` |
| `setTocUrl(string $url)` | `setTocBaseUrl(string $url)` |

### Custom Anchors

If you want to use your own logic for creating slugs for the headings, you can do so by using `setCreateAnchorIDCallback`.

Example using [cocur's slugify](https://github.com/cocur/slugify):

```php
$ParsedownToc->setCreateAnchorIDCallback(function($text, $level) {
    $slugify = new Slugify();
    return $slugify->slugify($text);
});
```
