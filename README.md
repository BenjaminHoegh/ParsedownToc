Here’s your **fully updated README** with all fixes + the “separate rendering” feature clearly integrated and polished:

---

<p align="center">
  <a href="https://github.com/BenjaminHoegh/ParsedownToc">
    <img alt="ParsedownToc" src="https://github.com/BenjaminHoegh/ParsedownToc/blob/master/.github/parsedownToc.png" height="330" />
  </a>
</p>

# ParsedownToc

![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/ParsedownToc.svg?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/benjaminhoegh/parsedown-toc)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownToc.svg?style=flat-square)

**ParsedownToc** is a table-of-contents extension for Parsedown and ParsedownExtra.
It automatically generates heading IDs, collects headings during parsing, and can replace a `[toc]` marker with a rendered table of contents.

---

## Features

* **Automatic ToC generation:** Replaces `[toc]` with a generated nested list of headings.
* **Flexible rendering:** Replace `[toc]` inline, or render the table of contents and Markdown body separately so they can be placed in different parts of your layout.
* **Configurable anchor generation:** Supports custom slug delimiters, replacements, transliteration, URL encoding, reserved IDs, and custom callbacks.
* **Custom heading IDs:** Respects explicit heading IDs and avoids reusing them for generated anchors.
* **ParsedownExtra support:** Works with Parsedown 1.8 and ParsedownExtra 0.9.

---

## Requirements

* PHP 8.2 or later.
* Parsedown 1.8 or later.
* Parsedown Extra 0.9 or later.

---

## Installation

Ensure you have Composer installed on your system.

### Using Composer

```bash
composer require benjaminhoegh/parsedown-toc
```

### Manual installation

Download the [latest release](https://github.com/BenjaminHoegh/ParsedownToc/releases/latest) and include:

```php
require 'Parsedown.php';
require 'ParsedownExtra.php';
require 'src/ParsedownToc.php';
```

---

## Quick Start

### Replace `[toc]` inside the document

```php
<?php
require 'vendor/autoload.php';

$content = file_get_contents('sample.md');
$parsedownToc = new ParsedownToc();

$html = $parsedownToc->text($content);

echo $html;
```

---

### Render the body and ToC in different places

Use `body()` and `contentsList()` when your layout needs the table of contents and main content in different places, such as a sidebar navigation and article body.

```php
<?php
require 'vendor/autoload.php';

$content = file_get_contents('sample.md');
$parsedownToc = new ParsedownToc();

$body = $parsedownToc->body($content);
$toc  = $parsedownToc->contentsList();
?>

<aside>
    <?= $toc ?>
</aside>

<main>
    <?= $body ?>
</main>
```

---

## Example

### Markdown input

```md
[toc]

## Introduction

### Installation

## Usage
```

### HTML output

```html
<div id="toc">
  <ul>
    <li><a href="#introduction">Introduction</a>
      <ul>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
  </ul>
</div>
```

---

## Configuration

There are two interchangeable ways to configure ParsedownToc:

* **`setOptions(array $options)`** — set multiple options at once
* **Individual setters** — configure options one by one

Both approaches are equivalent and can be mixed freely.

---

### Using `setOptions()` (bulk)

```php
<?php
$parsedownToc = new ParsedownToc();

$parsedownToc->setOptions([
    'heading_levels' => ['h2', 'h3'],
    'toc_items_limit' => 10,
    'slug_delimiter' => '_',
    'prefix' => 'md-',
    'reserved_ids' => ['introduction'],
]);
```

---

### Using individual setters (granular)

```php
<?php
$parsedownToc = new ParsedownToc();

$parsedownToc->setHeadingLevels(['h2', 'h3']);
$parsedownToc->setTocItemsLimit(10);
$parsedownToc->setSlugDelimiter('_');
$parsedownToc->setTocPrefix('md-');
$parsedownToc->setReservedIds(['introduction']);
```

---

### Custom anchor ID generator

```php
<?php
use Cocur\Slugify\Slugify;

$parsedownToc->setAnchorIdGenerator(function (string $text, array $options): string {
    $slugify = new Slugify();

    return $slugify->slugify($text);
});
```

This example uses `cocur/slugify`, but any callable returning a string can be used.
The returned ID still passes through ParsedownToc’s uniqueness checks, so duplicate IDs are avoided automatically.

---

## Available Options

| Option               | Type     | Default                           | Description                              |                                           |
| -------------------- | -------- | --------------------------------- | ---------------------------------------- | ----------------------------------------- |
| `heading_levels`     | `array`  | `['h1','h2','h3','h4','h5','h6']` | Headings included in the ToC             |                                           |
| `slug_delimiter`     | `string` | `-`                               | Delimiter for generated IDs              |                                           |
| `toc_items_limit`    | `int     | null`                             | `null`                                   | Max number of ToC items                   |
| `slug_lowercase`     | `bool`   | `true`                            | Lowercase generated IDs                  |                                           |
| `slug_replacements`  | `array   | null`                             | `null`                                   | String/regex replacements before slugging |
| `slug_transliterate` | `bool`   | `false`                           | Transliterate characters before slugging |                                           |
| `slug_urlencode`     | `bool`   | `false`                           | Apply `rawurlencode()`                   |                                           |
| `reserved_ids`       | `array`  | `[]`                              | Reserved IDs that won’t be reused        |                                           |
| `prefix`             | `string` | `''`                              | Prefix for heading ids links                     |                                           |
| `toc_tag`            | `string` | `[toc]`                           | Tag to replace                           |                                           |
| `toc_id`             | `string` | `toc`                             | Wrapper element ID                       |                                           |

---

## Methods

### Parsing

* `text(string $text): string`
  Parse Markdown and replace the ToC tag

* `body(string $text): string`
  Parse Markdown without replacing the ToC tag

* `contentsList(string $type = 'html')`
  Return ToC as `html`, `json`, or `array`

---

### Configuration

* `setOptions(array $options): void`
* `setHeadingLevels(array $levels): void`
* `setSlugDelimiter(string $delimiter): void`
* `setTocItemsLimit(?int $limit): void`
* `setSlugLowercase(bool $lowercase): void`
* `setSlugReplacements(?array $replacements): void`
* `setSlugTransliterate(bool $transliterate): void`
* `setSlugUrlencode(bool $urlencode): void`
* `setReservedIds(array $ids): void`
* `setTocPrefix(string $prefix): void`
* `setTocTag(string $tag): void`
* `setTocId(string $id): void`
* `setAnchorIdGenerator(callable $generator): void`
* `setCreateAnchorIDCallback(callable $callback): void` *(deprecated)*
