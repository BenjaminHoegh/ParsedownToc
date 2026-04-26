<p align="center">
  <a href="https://github.com/BenjaminHoegh/ParsedownToc">
    <img alt="ParsedownToc" src="https://github.com/BenjaminHoegh/ParsedownToc/blob/master/.github/parsedownToc.png" height="330" />
  </a>
</p>

# ParsedownToc

![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/ParsedownToc.svg?style=flat-square)
![Packagist Downloads](https://img.shields.io/packagist/dt/benjaminhoegh/parsedown-toc)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownToc.svg?style=flat-square)

A table-of-contents extension for [Parsedown](https://github.com/erusev/parsedown) and [ParsedownExtra](https://github.com/erusev/parsedown-extra). ParsedownToc automatically generates heading anchor IDs, collects headings during parsing, and can either replace a `[toc]` marker inline or expose the ToC separately for placement anywhere in your layout.

---

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Configuration](#configuration)
- [API Reference](#api-reference)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

---

## Features

- Replaces a `[toc]` marker in Markdown with a generated, nested HTML list of headings
- Supports separate rendering of body and ToC for sidebar or multi-column layouts
- Automatically generates unique, collision-free anchor IDs for every heading
- Respects explicit heading IDs set in Markdown (e.g. `## My Heading {#custom-id}` with ParsedownExtra)
- Configurable slug generation: delimiter, case, character replacements, transliteration, URL encoding, and prefix
- Accepts a fully custom anchor ID generator callable
- Returns the ToC as HTML, JSON, or a PHP array
- Compatible with Parsedown 1.8+ and ParsedownExtra 0.9+

---

## Requirements

| Requirement | Version |
|---|---|
| PHP | 8.2 or later |
| `ext-mbstring` | any |
| `ext-json` | any |
| Parsedown | 1.8 or later |
| ParsedownExtra *(optional)* | 0.9 or later |

---

## Installation

### Via Composer (recommended)

```bash
composer require benjaminhoegh/parsedown-toc
```

### Manual

Download the [latest release](https://github.com/BenjaminHoegh/ParsedownToc/releases/latest) and require the files directly:

```php
require 'Parsedown.php';
// require 'ParsedownExtra.php'; // optional
require 'src/ParsedownToc.php';
```

---

## Usage

### Inline ToC replacement

Place `[toc]` anywhere in your Markdown. `text()` will replace it with the rendered table of contents wrapped in a `<div id="toc">`.

```php
<?php
require 'vendor/autoload.php';

$markdown = <<<MD
[toc]

## Introduction

### Installation

## Usage
MD;

$parser = new ParsedownToc();
echo $parser->text($markdown);
```

**Output:**

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

<h2 id="introduction">Introduction</h2>
<h3 id="installation">Installation</h3>
<h2 id="usage">Usage</h2>
```

---

### Separate body and ToC rendering

Use `body()` and `getContentsList()` when the ToC and content need to appear in different parts of the page, such as a sidebar and a main content area.

```php
<?php
require 'vendor/autoload.php';

$markdown = file_get_contents('article.md');
$parser = new ParsedownToc();

$body = $parser->body($markdown);
$toc  = $parser->getContentsList();
?>
<aside>
    <?= $toc ?>
</aside>
<main>
    <?= $body ?>
</main>
```

> **Note:** `body()` must be called before `getContentsList()` on the same instance so that headings are collected before the ToC is rendered.

---

## Configuration

Options can be set in bulk with `setOptions()` or individually via dedicated setters. Both approaches are interchangeable and can be mixed freely.

### Bulk configuration

```php
$parser = new ParsedownToc();

$parser->setOptions([
    'heading_levels'  => ['h2', 'h3'],
    'delimiter'       => '_',
    'toc_items_limit' => 10,
    'lowercase'       => true,
    'prefix'          => 'heading-',
    'reserved_ids'    => ['toc', 'introduction'],
    'toc_tag'         => '[toc]',
    'toc_id'          => 'toc',
]);
```

### Individual setters

```php
$parser->setHeadingLevels(['h2', 'h3']);
$parser->setDelimiter('_');
$parser->setTocItemsLimit(10);
$parser->setLowercase(true);
$parser->setTocPrefix('heading-');
$parser->setReservedIds(['toc', 'introduction']);
$parser->setTocTag('[toc]');
$parser->setTocId('toc');
```

### Available options

| Option | Type | Default | Description |
|---|---|---|---|
| `heading_levels` | `string[]` | `['h1'…'h6']` | Which heading levels to include in the ToC |
| `delimiter` | `string` | `'-'` | Character used to replace spaces and non-alphanumeric characters in anchor IDs |
| `toc_items_limit` | `int\|null` | `null` | Maximum number of headings to include in the ToC; `null` means unlimited |
| `lowercase` | `bool` | `true` | Convert anchor IDs to lowercase |
| `replacements` | `array<string,string>\|null` | `null` | Map of strings or regex patterns to replace in the slug before sanitization |
| `transliterate` | `bool` | `false` | Transliterate non-ASCII characters to their ASCII equivalents before slugging |
| `urlencode` | `bool` | `false` | Apply `rawurlencode()` to the final anchor ID |
| `reserved_ids` | `string[]` | `[]` | Anchor IDs that must not be generated (treated as already taken) |
| `prefix` | `string` | `''` | String prepended to every generated anchor ID |
| `toc_tag` | `string` | `'[toc]'` | The marker in Markdown that is replaced with the ToC |
| `toc_id` | `string` | `'toc'` | The `id` attribute on the wrapper `<div>` when using inline replacement |

### Custom anchor ID generator

Replace the built-in slug logic entirely by supplying a callable. The callable receives the raw heading text and the current options array and must return a string. ParsedownToc still applies its uniqueness check to the returned value.

```php
$parser->setAnchorIdGenerator(function (string $text, array $options): string {
    return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $text));
});
```

### String/regex replacements

The `replacements` option accepts a map of search → replacement pairs. Values enclosed in regex delimiters (e.g. `/pattern/flags`) are treated as regular expressions; all other values are treated as literal strings.

```php
$parser->setReplacements([
    '&'     => 'and',  // literal replacement
    '/\s+/' => '-',    // regex replacement
]);
```

---

## API Reference

### Parsing

| Method | Returns | Description |
|---|---|---|
| `text(string $text)` | `string` | Parse Markdown and replace `[toc]` with the rendered table of contents |
| `body(string $text)` | `string` | Parse Markdown without replacing the `[toc]` marker |
| `getContentsList(string $type = 'html')` | `string\|array` | Return the collected ToC as `'html'`, `'json'`, or `'array'` |

### Configuration

| Method | Description |
|---|---|
| `setOptions(array $options)` | Set multiple options at once |
| `getOptions()` | Return the current options array |
| `getTocTag()` | Return the current ToC marker string |
| `setHeadingLevels(array $levels)` | Set which heading levels to include |
| `setDelimiter(string $delimiter)` | Set the slug delimiter character |
| `setTocItemsLimit(?int $limit)` | Set the maximum number of ToC entries |
| `setLowercase(bool $lowercase)` | Enable or disable lowercasing of anchor IDs |
| `setReplacements(?array $replacements)` | Set string/regex replacements for slug generation |
| `setTransliterate(bool $transliterate)` | Enable or disable transliteration |
| `setUrlencode(bool $urlencode)` | Enable or disable URL encoding of anchor IDs |
| `setReservedIds(array $ids)` | Set anchor IDs that must not be generated |
| `setTocPrefix(string $prefix)` | Set a prefix for all generated anchor IDs |
| `setTocTag(string $tag)` | Set the Markdown marker to replace with the ToC |
| `setTocId(string $id)` | Set the `id` attribute of the ToC wrapper element |
| `setAnchorIdGenerator(callable $generator)` | Provide a custom anchor ID generation callable |
| `setCreateAnchorIDCallback(callable $cb)` | **Deprecated since 2.0.** Use `setAnchorIdGenerator()` instead |

---

## Testing

The test suite uses [PHPUnit](https://phpunit.de/) 11.

```bash
composer install
vendor/bin/phpunit
```

Tests are located in the `tests/` directory and cover anchor ID generation, heading element handling, TOC tag processing, content list management, and setter validation.

---

## Contributing

Contributions are welcome. Please follow these steps:

1. Fork the repository and create a feature branch.
2. Write or update tests for your changes.
3. Ensure all tests pass: `vendor/bin/phpunit`
4. Check code style: `vendor/bin/php-cs-fixer fix --dry-run`
5. Run static analysis: `vendor/bin/psalm`
6. Open a pull request describing the change.

---

## License

ParsedownToc is open-source software released under the [MIT License](LICENSE).
