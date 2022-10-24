![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/parsedownToc.svg?style=flat-square)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/parsedownToc.svg?style=flat-square)

# ParsedownToc

---

Extension for Parsedown and ParsedownExtra

## Features

- Super fast

- Configurable

- Tested in PHP 7.1 to 8.0

- Full support for custom header ids

## Installation

Install the  [composer package](https://packagist.org/packages/hoegh/parsedowntoc "The ParsedownToc package on packagist.org"):

```
composer require benjaminhoegh/parsedowntoc
```

Or download the [latest release](https://github.com/BenjaminHoegh/parsedownToc/releases/latest "The latest release of parsedownToc") and include `Parsedown.php`

## Examples

```php
<?php
// Sample Markdown with '[toc]' tag included
$content = file_get_contents('sample.md');

$Parsedown = new ParsedownToC();

// Parses '[toc]' tag to ToC if exists
$html = $Parsedown->text($content);

echo $html;
```

With the `contentsList()` method, you can get just the "ToC".

```php
<?php
// Parse body and ToC separately
$content = file_get_contents('sample.md');
$Parsedown = new \ParsedownToC();

$body = $Parsedown->body($content);
$toc  = $Parsedown->contentsList();

echo $toc;  // Table of Contents in <ul> list
echo $body; // Main body
```

## Configuration

- **Main Class:** `ParsedownToC(array $options = null)`
  - **Optional arguments:**
    - `selectors`:
      
      - **Type:** `array`
      - **Default:** `['h1', 'h2', 'h3', 'h4', 'h5', 'h6']`
    
    - `delimiter`:
      
      - **Type:** `string`
      - **Default:** `-`
    
    - `limit`:
      
      - **Type:** `int`
      - **Default:** `null`
    
    - `lowercase`:
      
      - **Type:** `boolean`
      - **Default:** `true`
    
    - `replacements`:
      
      - **Type:** `array`
      - **Default:** `none`
    
    - `transliterate`:
      
      - **Type:** `boolean`
      - **Default:** `false`
    
    - `urlencode`:
      
      - Use PHP build-in `urlencode` this will disable all other options
      - **Type:** `boolean`
      - **Default:** `false`

    - `url`:

      - Prefixes anchor with the specified URL
      - **Type:** `string`
      - **Default:** ``

  - **Methods:**
    - `text(string $text)`:
      - Returns the parsed content and `[toc]` tag(s) parsed as well.
    - `body(string $text)`:
      - Returns the parsed content WITHOUT parsing `[toc]` tag.
    - `contentsList([string $type_return='html'])`:
      - Returns the ToC, the table of contents, in HTML, JSON or as an array.
      - **Optional argument:**
        - `$type_return`:
          - `html`, `json`, or `array` can be specified.
          - **Default:** `html`
      - Alias method: `contentsList(string $type_return)`
    - `setTagToc(string $tag='[tag]')`:
      - Sets user defined ToC markdown tag. Use this method before `text()` or `body()` method if you want to use the ToC tag rather than the "`[toc]`".
      - Empty value sets the default ToC tag.
