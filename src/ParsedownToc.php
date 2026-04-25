<?php

declare(strict_types=1);

/**
 * This code checks if the class 'ParsedownExtra' exists. If it does, it creates an alias for it called 'ParsedownTocParentAlias'.
 * If 'ParsedownExtra' does not exist, it creates an alias for 'Parsedown' called 'ParsedownTocParentAlias'.
 */

if (class_exists('ParsedownExtra')) {
    class_alias('ParsedownExtra', 'ParsedownTocParentAlias');
} else {
    class_alias('Parsedown', 'ParsedownTocParentAlias');
}

class ParsedownToc extends ParsedownTocParentAlias
{
    public const VERSION = '2.0.0';
    public const VERSION_PARSEDOWN_REQUIRED = '1.8.0';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.9.0';
    public const MIN_PHP_VERSION = '8.2';

    protected array $options = [];
    protected array $defaultOptions = array(
        'heading_levels' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        'slug_delimiter' => '-',
        'toc_items_limit' => null,
        'slug_lowercase' => true,
        'slug_replacements' => null,
        'slug_transliterate' => false,
        'slug_urlencode' => false,
        'reserved_ids' => [],
        'prefix' => '',
        'toc_tag' => '[toc]',
        'toc_id' => 'toc',
    );

    private array $anchorDuplicates = [];
    private array $contentsListArray = [];
    private $createAnchorIDCallback = null;

    public function __construct()
    {

        // Check if PHP version is supported
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION) < 0) {
            $msg_error  = 'Version Error.' . PHP_EOL;
            $msg_error .= '  ParsedownToc requires PHP version ' . self::MIN_PHP_VERSION . ' or later.' . PHP_EOL;
            $msg_error .= '  - Current version : ' . PHP_VERSION . PHP_EOL;
            $msg_error .= '  - Required version: ' . self::MIN_PHP_VERSION . PHP_EOL;
            throw new Exception($msg_error);
        }

        // Check if Parsedown version is supported
        if (version_compare(\Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED) < 0) {
            $msg_error  = 'Version Error.' . PHP_EOL;
            $msg_error .= '  ParsedownToc requires a later version of Parsedown.' . PHP_EOL;
            $msg_error .= '  - Current version : ' . \Parsedown::version . PHP_EOL;
            $msg_error .= '  - Required version: ' . self::VERSION_PARSEDOWN_REQUIRED . ' and later' . PHP_EOL;
            throw new Exception($msg_error);
        }

        # If ParsedownExtra is installed, check its version
        if (class_exists('ParsedownExtra')) {
            if (version_compare(\ParsedownExtra::version, self::VERSION_PARSEDOWN_EXTRA_REQUIRED) < 0) {
                $msg_error  = 'Version Error.' . PHP_EOL;
                $msg_error .= '  ParsedownToc requires a later version of ParsedownExtra.' . PHP_EOL;
                $msg_error .= '  - Current version : ' . \ParsedownExtra::version . PHP_EOL;
                $msg_error .= '  - Required version: ' . self::VERSION_PARSEDOWN_EXTRA_REQUIRED . ' and later' . PHP_EOL;
                throw new Exception($msg_error);
            }
        }

        $parentClass = get_parent_class($this);

        if (is_string($parentClass) && method_exists($parentClass, '__construct')) {
            $constructor = new ReflectionMethod($parentClass, '__construct');
            $constructor->invoke($this);
        }

        // Initialize default options
        $this->options = $this->defaultOptions;
    }

    /**
     * Set options for the ParsedownToc parser.
     *
     * @param array $options The options to set.
     * @return void
     */
    public function setOptions(array $options): void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set the heading_levels option.
     *
     * @param array $heading_levels The heading levels to set.
     * @return void
     */
    public function setHeadingLevels(array $heading_levels): void
    {
        $this->options['heading_levels'] = $heading_levels;
    }

    /**
     * Set the slug_delimiter option.
     *
     * @param string $slug_delimiter The slug delimiter to set.
     * @return void
     */
    public function setSlugDelimiter(string $slug_delimiter): void
    {
        $this->options['slug_delimiter'] = $slug_delimiter;
    }

    /**
     * Set the toc_items_limit option.
     *
     * @param int|null $toc_items_limit The TOC item limit to set.
     * @return void
     */
    public function setTocItemsLimit(?int $toc_items_limit): void
    {
        $this->options['toc_items_limit'] = $toc_items_limit;
    }

    /**
     * Set the slug_lowercase option.
     *
     * @param bool $slug_lowercase The slug lowercase option to set.
     * @return void
     */
    public function setSlugLowercase(bool $slug_lowercase): void
    {
        $this->options['slug_lowercase'] = $slug_lowercase;
    }

    /**
     * Set the slug_replacements option.
     *
     * @param array|null $slug_replacements The slug replacements to set.
     * @return void
     */
    public function setSlugReplacements(?array $slug_replacements): void
    {
        $this->options['slug_replacements'] = $slug_replacements;
    }

    /**
     * Set the slug_transliterate option.
     *
     * @param bool $slug_transliterate The slug transliterate option to set.
     * @return void
     */
    public function setSlugTransliterate(bool $slug_transliterate): void
    {
        $this->options['slug_transliterate'] = $slug_transliterate;
    }

    /**
     * Set the slug_urlencode option.
     *
     * @param bool $slug_urlencode The slug urlencode option to set.
     * @return void
     */
    public function setSlugUrlencode(bool $slug_urlencode): void
    {
        $this->options['slug_urlencode'] = $slug_urlencode;
    }

    /**
     * Set the reserved_ids option.
     *
     * @param array $reserved_ids The reserved ids to set.
     * @return void
     */
    public function setReservedIds(array $reserved_ids): void
    {
        $this->options['reserved_ids'] = $reserved_ids;
    }

    /**
     * Set the prefix option.
     *
     * @param string $prefix The prefix to set.
     * @return void
     */
    public function setTocPrefix(string $prefix): void
    {
        $this->options['prefix'] = $prefix;
    }

    /**
     * Set the toc_tag option.
     *
     * @param string $toc_tag The toc_tag to set.
     * @return void
     */
    public function setTocTag(string $toc_tag): void
    {
        $this->options['toc_tag'] = $toc_tag;
    }

    /**
     * Set the toc_id option.
     *
     * @param string $toc_id The toc_id to set.
     * @return void
     */
    public function setTocId(string $toc_id): void
    {
        $this->options['toc_id'] = $toc_id;
    }


    /**
     * Returns the options of the ParsedownToc object.
     *
     * @return array The options of the ParsedownToc object.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Heading process.
     * Creates heading block element and stores to the ToC list. It overrides
     * the parent method: \Parsedown::blockHeader() and returns $Block array if
     * the $Line is a heading element.
     *
     * @param  array $Line  Array that Parsedown detected as a block type element.
     * @return void|array   Array of Heading Block.
     */
    protected function blockHeader($Line)
    {
        // Use parent blockHeader method to process the $Line to $Block
        $Block = parent::blockHeader($Line);

        if (!empty($Block)) {
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];

            // Check if heading level is in the selectors
            if (!in_array($level, $this->options['heading_levels'], true)) {
                return $Block;
            }

            $attributes = $Block['element']['attributes'] ?? [];
            $hasCustomId = isset($attributes['id']);
            $id = $hasCustomId ? $attributes['id'] : $this->createAnchorID($text);

            if ($hasCustomId) {
                $this->reserveAnchorID($id);
            }

            $attributes['id'] = $id;
            $Block['element']['attributes'] = $attributes;
            $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

            return $Block;
        }
    }

    /**
     * Heading process.
     * Creates heading block element and stores to the ToC list. It overrides
     * the parent method: \Parsedown::blockSetextHeader() and returns $Block array if
     * the $Line is a heading element.
     *
     * @param  array $Line Array that Parsedown detected as a block type element.
     * @return void|array Array of Heading Block.
     */
    protected function blockSetextHeader($Line, array $Block = null)
    {
        // Use parent blockHeader method to process the $Line to $Block
        $Block = parent::blockSetextHeader($Line, $Block);

        if (!empty($Block)) {
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];

            // Check if heading level is in the selectors
            if (!in_array($level, $this->options['heading_levels'], true)) {
                return $Block;
            }

            $attributes = $Block['element']['attributes'] ?? [];
            $hasCustomId = isset($attributes['id']);
            $id = $hasCustomId ? $attributes['id'] : $this->createAnchorID($text);

            if ($hasCustomId) {
                $this->reserveAnchorID($id);
            }

            $attributes['id'] = $id;
            $Block['element']['attributes'] = $attributes;

            $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

            return $Block;
        }
    }

    /**
     * Parses the given markdown string to an HTML string, but it leaves the ToC
     * tag as is. It's an alias of the parent method "\parent::text()".
     *
     * @param  string $text  Markdown string to be parsed.
     * @return string        Parsed HTML string.
     */
    public function body(string $text): string
    {
        $this->resetParserState();

        return $this->renderMarkdown($text);
    }

    /**
     * Returns the parsed ToC.
     * If the arg is "string" then it returns the ToC in HTML string.
     *
     * @param  string $type_return Type of the return format. "string" or "json".
     * @return string|array HTML/JSON string of ToC.
     */
    public function contentsList(string $type_return = 'html')
    {
        switch (strtolower($type_return)) {
            case 'string':
            case 'html':
                return $this->renderContentsListHtml();

            case 'json':
                try {
                    return json_encode($this->contentsListArray, JSON_THROW_ON_ERROR);
                } catch (\JsonException $exception) {
                    throw new RuntimeException('Failed to encode table of contents as JSON.', 0, $exception);
                }

            case 'array':
                return $this->contentsListArray;

            default:
                $backtrace = debug_backtrace();
                $caller = $backtrace[0];
                $errorMessage = "Unknown return type '{$type_return}' given while parsing ToC. Called in " . $caller['file'] . " on line " . $caller['line'];
                throw new InvalidArgumentException($errorMessage);
        }
    }

    protected function renderContentsListHtml(): string
    {
        if (empty($this->contentsListArray)) {
            return '';
        }

        $tree = [];
        $stack = [];

        foreach ($this->contentsListArray as $Content) {
            $level = (int) trim($Content['level'], 'h');

            $node = [
                'content' => $Content,
                'children' => [],
            ];

            /**
             * Pop until we find the nearest previous heading with a lower level.
             *
             * Example:
             * h2 A
             * h4 B
             * h4 C
             *
             * When C is processed, B is popped because h4 >= h4,
             * so C becomes a sibling of B under A.
             */
            while (!empty($stack) && $stack[array_key_last($stack)]['level'] >= $level) {
                array_pop($stack);
            }

            if (empty($stack)) {
                $tree[] = $node;

                $lastIndex = array_key_last($tree);
                $stack[] = [
                    'level' => $level,
                    'node' => &$tree[$lastIndex],
                ];

                continue;
            }

            $parentIndex = array_key_last($stack);
            $parent = &$stack[$parentIndex]['node'];

            $parent['children'][] = $node;

            $childIndex = array_key_last($parent['children']);
            $stack[] = [
                'level' => $level,
                'node' => &$parent['children'][$childIndex],
            ];

            unset($parent);
        }

        return $this->renderContentsListNodes($tree);
    }

    protected function renderContentsListNodes(array $nodes): string
    {
        if (empty($nodes)) {
            return '';
        }

        $html = '<ul>' . PHP_EOL;

        foreach ($nodes as $node) {
            $Content = $node['content'];

            $text = $this->fetchText($Content['text']);
            $id = (string) $Content['id'];
            $level = (int) trim($Content['level'], 'h');

            $href = $this->options['prefix'] . '#' . $id;

            $html .= sprintf(
                '<li class="toc-level-%d"><a href="%s">%s</a>',
                $level,
                htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );

            if (!empty($node['children'])) {
                $html .= PHP_EOL . $this->renderContentsListNodes($node['children']);
            }

            $html .= '</li>' . PHP_EOL;
        }

        $html .= '</ul>' . PHP_EOL;

        return $html;
    }

    /**
     * Allows users to define their own logic for createAnchorID.
     */
    public function setCreateAnchorIDCallback(callable $callback): void
    {
        $this->createAnchorIDCallback = $callback;
    }


    /**
     * Creates an anchor ID for the given text.
     *
     * If a callback is provided, it uses the user-defined logic to create the anchor ID.
     * Otherwise, it uses the default logic which involves normalizing the string, replacing characters, and sanitizing the anchor.
     *
     * @param string $text The text for which to create the anchor ID.
     * @return string The created anchor ID.
     */
    protected function createAnchorID(string $text): string
    {
        // Use user-defined logic if a callback is provided
        if (is_callable($this->createAnchorIDCallback)) {
            $text = (string) call_user_func($this->createAnchorIDCallback, $text, $this->options);

            return $this->finalizeAnchorID($text);
        }

        if ($this->options['slug_urlencode']) {
            $text = urlencode($text);

            return $this->finalizeAnchorID($text);
        }

        // Lowercase the string
        $text = $this->options['slug_lowercase'] ? mb_strtolower($text, 'UTF-8') : $text;

        // Make custom replacements
        if (!empty($this->options['slug_replacements'])) {
            $text = $this->applyReplacements($text, $this->options['slug_replacements']);
        }

        // Remove non UTF-8 characters
        $text = $this->normalizeString($text);

        // Transliterate characters to ASCII
        if ($this->options['slug_transliterate']) {
            $text = $this->transliterate($text);
        }

        // Sanitize the anchor
        $text = $this->sanitizeAnchor($text);

        return $this->finalizeAnchorID($text);
    }

    /**
     * Apply configured replacements using either plain-string or regex patterns.
     *
     * @param string $text
     * @param array $replacements
     * @return string
     */
    protected function applyReplacements(string $text, array $replacements): string
    {
        foreach ($replacements as $search => $replacement) {
            $search = (string) $search;
            $replacement = (string) $replacement;

            if ($this->isRegexPattern($search)) {
                $result = preg_replace($search, $replacement, $text);

                if ($result !== null) {
                    $text = $result;
                }

                continue;
            }

            $text = str_replace($search, $replacement, $text);
        }

        return $text;
    }

    /**
     * Determine whether a replacement key looks like a delimited regex pattern.
     */
    protected function isRegexPattern(string $pattern): bool
    {
        if ($pattern === '') {
            return false;
        }

        $delimiter = $pattern[0];

        if (ctype_alnum($delimiter) || $delimiter === '\\') {
            return false;
        }

        for ($index = strlen($pattern) - 1; $index > 0; $index--) {
            if ($pattern[$index] !== $delimiter || $pattern[$index - 1] === '\\') {
                continue;
            }

            $modifiers = substr($pattern, $index + 1);

            return preg_match('/^[imsxeADSUXJu]*$/', $modifiers) === 1;
        }

        return false;
    }

    /**
     * Finalize an anchor ID by ensuring a usable base and applying uniqueness.
     */
    protected function finalizeAnchorID(string $text): string
    {
        if ($text === '') {
            $text = 'section';
        }

        return $this->uniquifyAnchorID($text);
    }

    /**
     * Normalize a string by converting it to encoding it to UTF-8.
     *
     * @param string $text The string to be normalized.
     *
     * @return array|false|string
     */
    protected function normalizeString(string $text)
    {
        return mb_convert_encoding($text, 'UTF-8', mb_list_encodings());
    }

    /**
     * Replaces special characters in a string with their corresponding ASCII equivalents.
     *
     * @param  string $text The input string.
     * @return string The modified string with replaced characters.
     */
    protected function transliterate(string $text): string
    {
        $characterMap = [
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'AA', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'OE', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'aa', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'oe', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y',

            // Latin symbols
            '©' => '(c)', '®' => '(r)', '™' => '(tm)',

            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => 'TH',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => 'X', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'O',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'O', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => 'th',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => 'x', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'o',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'o', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',

            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ğ' => 'g',

            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'Kh', 'Ц' => 'Ts',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => 'U', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'kh', 'ц' => 'ts',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => 'u', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
            'я' => 'ya',

            // Ukrainian
            'Є' => 'Ye', 'І' => 'I', 'Ї' => 'Yi', 'Ґ' => 'G',
            'є' => 'ye', 'і' => 'i', 'ї' => 'yi', 'ґ' => 'g',

            // Czech
            'Č' => 'C', 'Ď' => 'D', 'Ě' => 'E', 'Ň' => 'N', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ů' => 'U',
            'Ž' => 'Z',
            'č' => 'c', 'ď' => 'd', 'ě' => 'e', 'ň' => 'n', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ů' => 'u',
            'ž' => 'z',

            // Polish
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',

            // Latvian
            'Ā' => 'A', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'I', 'Ķ' => 'K', 'Ļ' => 'L', 'Ņ' => 'N', 'Ū' => 'U',
            'ā' => 'a', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n', 'ū' => 'u',
        ];

        return strtr($text, $characterMap);
    }

    /**
     * Sanitizes an anchor text by removing special characters, replacing spaces with dashes,
     * and removing consecutive dashes.
     *
     * @param  string $text The anchor text to sanitize.
     * @return string The sanitized anchor text.
     */
    protected function sanitizeAnchor(string $text): string
    {
        $delimiter = $this->options['slug_delimiter'];
        // Replace non-alphanumeric characters with our delimiter
        $text = preg_replace('/[^\p{L}\p{Nd}]+/u', $delimiter, $text);
        // Remove consecutive delimiters
        $text = preg_replace('/(' . preg_quote($delimiter, '/') . '){2,}/', '$1', $text);
        // Remove leading and trailing delimiters
        return trim($text, $delimiter);
    }

    /**
     * Generate a unique anchor ID based on the given text.
     *
     * @param  string $text The text to generate the anchor ID from.
     * @return string The unique anchor ID.
     */
    protected function uniquifyAnchorID(string $text): string
    {
        $reserved_ids = $this->options['reserved_ids'];

        // Initialize the count for this text if not already set
        if (!isset($this->anchorDuplicates[$text])) {
            $this->anchorDuplicates[$text] = 0;
        }

        // If the text is not in the blacklist and is the first time we see it, return it as is
        if (!in_array($text, $reserved_ids, true) && $this->anchorDuplicates[$text] === 0) {
            // Increment here to account for the next time we see this text
            $this->anchorDuplicates[$text]++;
            return $text; // Return without adding a count
        }

        // For subsequent duplicates, start appending a number starting from 1
        $originalText = $text;

        /**
         * @psalm-suppress all
         * Workaround for Psalm as UnsupportedPropertyReferenceUsage can't be suppressed
         */
        $count = &$this->anchorDuplicates[$originalText];

        // Generate a unique anchor ID by appending a count to the original text
        while (true) {
            if ($count > 0) { // Only append the count if it's not the first occurrence
                $text = $originalText . '-' . $count;
                if (!in_array($text, $reserved_ids, true) && !isset($this->anchorDuplicates[$text])) {
                    break;
                }
            }
            $count++;
        }

        // Increment the count for the next duplicate
        $this->anchorDuplicates[$text] = 1; // Initialize the duplicate counter for the new unique text
        $count++; // Prepare for the next potential duplicate

        return $text;
    }

    /**
     * Reserve an anchor ID so later generated IDs do not reuse it.
     */
    protected function reserveAnchorID(string $id): void
    {
        if (!isset($this->anchorDuplicates[$id])) {
            $this->anchorDuplicates[$id] = 1;
        }
    }

    /**
     * Reset per-document parsing state before processing a new Markdown document.
     */
    protected function resetParserState(): void
    {
        $this->anchorDuplicates = [];
        $this->contentsListArray = [];
    }

    /**
     * Parse markdown while preserving current parser state.
     */
    protected function renderMarkdown(string $text): string
    {
        $text = $this->encodeTagToHash($text);
        $html = parent::text($text);

        return $this->decodeTagFromHash($html);
    }



    /**
     * Decodes the hashed ToC tag to an original tag and replaces.
     *
     * This is used to avoid parsing user defined ToC tag which includes "_" in
     * their tag such as "[[_]]". Unless it will be parsed as:
     *   "<p>[[<em>TOC</em>]]</p>"
     *
     * @param  string $text
     * @return string
     */
    protected function decodeTagFromHash(string $text): string
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTocTag();
        $tag_hashed = hash('sha256', $salt . $tag_origin);

        if (!str_contains($text, $tag_hashed)) {
            return $text;
        }

        return str_replace($tag_hashed, $tag_origin, $text);
    }

    /**
     * Encodes the ToC tag to a hashed tag and replace.
     *
     * This is used to avoid parsing user defined ToC tag which includes "_" in
     * their tag such as "[[_]]". Unless it will be parsed as:
     *   "<p>[[<em>TOC</em>]]</p>"
     *
     * @param  string $text
     * @return string
     */
    protected function encodeTagToHash(string $text): string
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTocTag();

        if (!str_contains($text, $tag_origin)) {
            return $text;
        }

        $tag_hashed = hash('sha256', $salt . $tag_origin);

        return str_replace($tag_origin, $tag_hashed, $text);
    }

    /**
     * Get only the text from a markdown string.
     * It parses to HTML once then trims the tags to get the text.
     *
     * @param  string $text  Markdown text.
     * @return string
     */
    protected function fetchText(string $text): string
    {
        return trim(strip_tags($this->line($text)));
    }

    /**
     * Gets the ID attribute of the ToC for HTML tags.
     *
     * @return string
     */
    protected function getTocIdAttribute(): string
    {
        return $this->options['toc_id'];
    }

    /**
     * Unique string to use as a salt value.
     *
     * @return string
     */
    protected function getSalt(): string
    {
        static $salt;
        if (isset($salt)) {
            return $salt;
        }

        $salt = hash('md5', strval(time()));
        return $salt;
    }

    /**
     * Gets the Markdown tag for ToC.
     *
     * @return string
     */
    protected function getTocTag(): string
    {
        return $this->options['toc_tag'];
    }

    /**
     * Set/stores the heading block to ToC list in a string and array format.
     *
     * @param  array $Content   Heading info such as "level","id" and "text".
     * @return void
     */
    protected function setContentsList(array $Content): void
    {
        if ($this->options['toc_items_limit'] !== null && count($this->contentsListArray) >= $this->options['toc_items_limit']) {
            return;
        }

        $this->setContentsListAsArray($Content);
    }

    /**
     * Sets/stores the heading block info as an array.
     *
     * @param  array $Content
     * @return void
     */
    protected function setContentsListAsArray(array $Content): void
    {
        $this->contentsListArray[] = $Content;
    }

    /**
     * Parses markdown string to HTML and also the "[toc]" tag as well.
     * It overrides the parent method: \Parsedown::text().
     *
     * @param  string $text
     * @return string
     */
    public function text($text): string
    {
        // Parses the Markdown text except the ToC tag. This also searches
        // the list of contents and available to get from "contentsList()"
        // method.
        $this->resetParserState();
        $html = $this->renderMarkdown($text);

        $tag_origin  = $this->getTocTag();

        if (!str_contains($text, $tag_origin)) {
            return $html;
        }

        $data = $this->contentsList();
        $toc_id   = $this->getTocIdAttribute();
        $escapedTocId = htmlspecialchars($toc_id, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $needle  = '<p>' . $tag_origin . '</p>';
        $replace = "<div id=\"{$escapedTocId}\">{$data}</div>";

        return str_replace($needle, $replace, $html);
    }
}
