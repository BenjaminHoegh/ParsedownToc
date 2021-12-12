<p align="center">
  <a href="https://github.com/BenjaminHoegh/ParsedownToc">
    <img alt="ParsedownToc" src="https://github.com/BenjaminHoegh/ParsedownToc/blob/master/.github/ParsedownToc.png" height="330" />
  </a>

  <h3 align="center">Parsedown ToC</h3>

  <p align="center">
    <a href="https://benjaminhoegh.github.io/ParsedownToc/configurations"><strong>Explore Documentation »</strong></a>
    <br>
    <br>
    <a href="https://github.com/BenjaminHoegh/ParsedownToc/issues/new?template=bug_report.md">Report bug</a>
    ·
    <a href="https://github.com/BenjaminHoegh/ParsedownToc/issues/new?template=feature_request.md&labels=feature">Request feature</a>
    ·
    <a href="https://github.com/BenjaminHoegh/ParsedownToc/discussions">Discussions</a>
  </p>

</p>

<br>

[![Github All Releases](https://img.shields.io/github/release/BenjaminHoegh/ParsedownToc.svg?style=flat-square)](https://github.com/BenjaminHoegh/ParsedownToc/releases) [![GitHub](https://img.shields.io/github/license/BenjaminHoegh/ParsedownToc?style=flat-square)](https://github.com/BenjaminHoegh/ParsedownToc/blob/main/LICENSE.md)

Table of contents

- [Getting started](#getting-started)
- [Bugs and feature requests](#bugs-and-feature-requests)
- [Contributing](#contributing)
- [Community](#community)
- [Copyright and license](#copyright-and-license)

## Features
- Super fast
- Configurable
- Tested in PHP 7.1 to 8.1
- Full support for custom header ids

## Installation

Install the (composer package)[]:

```shell
composer require benjaminhoegh/parsedown-toc
```

Or download the (latest release)[] and include ParsedownToc.php

## Examples

<!-- TODO: Update examples -->

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
  - **Methods:**
    - `text(string $text)`:
      - Returns the parsed content and `[toc]` tag(s) parsed as well.
    - `body(string $text)`:
      - Returns the parsed content WITHOUT parsing `[toc]` tag.
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

## Bugs and feature requests

Have a bug or a feature request? Please first read the [issue guidelines](https://github.com/BenjaminHoegh/ParsedownToc/blob/main/.github/CONTRIBUTING.md#using-the-issue-tracker) and search for existing and closed issues. If your problem or idea is not addressed yet, [please open a new issue](https://github.com/BenjaminHoegh/ParsedownToc/issues/new/choose).

## Contributing

Please read through our [contributing guidelines](https://github.com/BenjaminHoegh/ParsedownToc/blob/main/.github/CONTRIBUTING.md). Included are directions for opening issues, coding standards, and notes on development.

All PHP should conform to the [Code Guide](https://www.php-fig.org/psr/psr-12/).

## Community

Get updates on ParsedownToc's development and chat with the project maintainers and community members.

- Join [GitHub discussions](https://github.com/BenjaminHoegh/ParsedownToc/discussions).

## Copyright and license

Code and documentation copyright 2021 the [ParsedownToc Authors](https://github.com/BenjaminHoegh/ParsedownToc/graphs/contributors). Code released under the [MIT License](https://github.com/BenjaminHoegh/ParsedownToc/blob/main/LICENSE.md). Docs released under [Creative Commons](https://github.com/BenjaminHoegh/ParsedownToc/blob/main/docs/LICENSE.md).
