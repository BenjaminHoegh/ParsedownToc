<p align="center">
  <a href="https://github.com/BenjaminHoegh/ParsedownToc">
    <img alt="ParsedownToc" src="https://github.com/BenjaminHoegh/ParsedownToc/blob/master/.github/parsedownToc.png" height="330" />
  </a>
</p>

# ParsedownToc

![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/ParsedownToc.svg?style=flat-square)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownToc.svg?style=flat-square)

**ParsedownToc** is an extension for Parsedown and ParsedownExtra that introduces advanced features for developers working with Markdown. It is based on [@KEINOS toc extention](https://github.com/KEINOS/parsedown-extension_table-of-contents)

> [!NOTE]
> Does not yet include the latest changes in ParsedownExtended v1.2.0

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

| Option         | Type     | Default                                 | Description                                                   |
|----------------|----------|-----------------------------------------|---------------------------------------------------------------|
| selectors      | array    | ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']    |                                                               |
| delimiter      | string   | `-`                                     |                                                               |
| limit          | int      | `null`                                  |                                                               |
| lowercase      | boolean  | `true`                                  |                                                               |
| replacements   | array    | none                                    |                                                               |
| transliterate  | boolean  | `false`                                 |                                                               |
| urlencode      | boolean  | `false`                                 | Uses PHP built-in `urlencode` and disables all other options. |
| url            | string   | ``                                      | Prefixes anchor with the specified URL.                       |

### Methods:

The ParsedownToc class offers several methods for different functionalities:

- **text(string $text):** Returns the parsed content and `[toc]` tag(s).
- **body(string $text):** Returns the parsed content without the `[toc]` tag.
- **contentsList([string $type_return='html']):** Returns the ToC in HTML, JSON, or as an array.
    - _Optional:_ Specify the return type as `html`, `json`, or `array`.
- **setTocSelectors(array $array):** Allows you to set specific selectors.
- **setTocDelimiter(string $delimiter):** Define a custom delimiter.
- **setTocLimit(int $limit):** Set a limit for the table of contents.
- **setTocLowercase(bool $boolean):** Choose whether the output should be in lowercase.
- **setTocReplacements(array $replacements):** Provide replacements for specific content.
- **setTocTransliterate(bool $boolean):** Specify if transliterations should be made.
- **setTocUrlencode(bool $boolean):** Decide if you want to use PHP's built-in `urlencode`.
- **setTocBlacklist(array $blacklist):** Blacklist specific IDs from header anchor generation.
- **setTocPrefix(string $url):** Set a specific URL prefix for anchors.
- **setTocTag(string $tag='[tag]'):** Set a custom ToC markdown tag.
- **setTocId(string $id):** Set a custom ID for the table of contents.

### Custom Anchors

If you want to use your own logic for creating slugs for the headings, you can do so by using `setCreateAnchorIDCallback`.

Example using [cocur's slugify](https://github.com/cocur/slugify):

```php
$ParsedownToc->setCreateAnchorIDCallback(function($text, $level) {
    $slugify = new Slugify();
    return $slugify->slugify($text);
});
```
