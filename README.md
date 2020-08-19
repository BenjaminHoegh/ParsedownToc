![GitHub release](https://img.shields.io/github/release/BenjaminHoegh/parsedownToc.svg?style=flat-square)
![GitHub](https://img.shields.io/github/license/BenjaminHoegh/parsedownToc.svg?style=flat-square)

# ParsedownToc

---

Extension for Parsedown and ParsedownExtra



```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

// Sample Markdown with '[toc]' tag included
$fileContent = file_get_contents('SAMPLE.md');

$Parsedown = new \ParsedownToc();

// Parses '[toc]' tag to ToC if exists
$html = $Parsedown->text($fileContent);

echo $html;
```

With the `contentsList()` method, you can get just the "ToC".

```php
<?php
// Parse body and ToC separately

require_once __DIR__ . '/vendor/autoload.php';

$fileContent = file_get_contents('SAMPLE.md');
$Parsedown = new \ParsedownToC();

$body = $Parsedown->body($fileContent);
$toc  = $Parsedown->contentsList();

echo $toc;  // Table of Contents in <ul> list
echo $body; // Main body
```

- **Main Class:** `ParsedownToc(array $options = null)`
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
      - Use PHP urlencode this disable all other options
      - **Type:** `boolean`
      - **Default:** `false`
  - **Methods:**
    - `text(string $text)`:
      - Returns the parsed content and `[toc]` tag(s) parsed as well.
      - Required argument `$text`: Markdown string to be parsed.
    - `body(string $text)`:
      - Returns the parsed content WITHOUT parsing `[toc]` tag.
      - Required argument `$text`: Markdown string to be parsed.
    - `contentsList([string $type_return='html'])`:
      - Returns the ToC, the table of contents, in HTML or JSON.
      - **Optional argument:**
        - `$type_return`:
          - `html` or `json` can be specified.
          - **Default:** `html`
      - Alias method: `contentsList(string $type_return)`
    - `setTagToc(string $tag='[tag]')`:
      - Sets user defined ToC markdown tag. Use this method before `text()` or `body()` method if you want to use the ToC tag rather than the "`[toc]`".
      - Empty value sets the default ToC tag.

## Install

### Via Composer

If you are familiar to [composer](https://en.wikipedia.org/wiki/Composer_(software)), the package manager for PHP, then install it as below:

```bash
# Latest stable release
composer require BenjaminHoegh/parsedownToc
```

### Manual Install (Download the script)

Download the '[ParsedownToc.php](BenjaminHoegh/parsedownToc/blob/master/ParsedownToc.php)' file and place it anywhere you like to include.
