<?php

declare(strict_types=1);

if (!class_exists('ParsedownTocParentAlias', false)) {
    if (class_exists('ParsedownExtra')) {
        class_alias('ParsedownExtra', 'ParsedownTocParentAlias');
    } elseif (class_exists('Parsedown')) {
        class_alias('Parsedown', 'ParsedownTocParentAlias');
    } else {
        throw new \LogicException('Parsedown or ParsedownExtra must be installed before ParsedownToc is loaded.');
    }
}

class ParsedownToc extends ParsedownTocParentAlias
{
    public const VERSION = '2.0.0';
    public const VERSION_PARSEDOWN_REQUIRED = '1.8.0';
    public const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.9.0';
    public const MIN_PHP_VERSION = '8.2';

    /** @var array<string, mixed> */
    private const DEFAULT_OPTIONS = [
        'heading_levels' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        'delimiter' => '-',
        'toc_items_limit' => null,
        'lowercase' => true,
        'replacements' => null,
        'transliterate' => false,
        'urlencode' => false,
        'reserved_ids' => [],
        'prefix' => '',
        'toc_tag' => '[toc]',
        'toc_id' => 'toc',
    ];

    private const ALLOWED_HEADING_LEVELS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    /** @var array<string, mixed> */
    protected array $options = self::DEFAULT_OPTIONS;

    /** @var array<string, int> */
    private array $anchorDuplicates = [];

    /** @var array<int, array{id: string, text: string, level: string}> */
    private array $contentsListArray = [];

    /**
     * Custom anchor ID generator provided by package users.
     *
     * @var ?callable(string, array<string, mixed>): string
     */
    /** @psalm-suppress MissingPropertyType */
    private $anchorIdGenerator = null;

    private ?string $salt = null;

    public function __construct()
    {
        $this->assertRuntimeRequirements();
        $this->callParentConstructor();
    }

    /**
     * @param array<string, mixed> $options
     */
    public function setOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            match ($name) {
                'heading_levels' => $this->setHeadingLevels($this->assertStringList($value, $name)),
                'delimiter' => $this->setDelimiter($this->assertString($value, $name)),
                'toc_items_limit' => $this->setTocItemsLimit($this->assertNullableNonNegativeInt($value, $name)),
                'lowercase' => $this->setLowercase($this->assertBool($value, $name)),
                'replacements' => $this->setReplacements($this->assertNullableStringMap($value, $name)),
                'transliterate' => $this->setTransliterate($this->assertBool($value, $name)),
                'urlencode' => $this->setUrlencode($this->assertBool($value, $name)),
                'reserved_ids' => $this->setReservedIds($this->assertStringList($value, $name)),
                'prefix' => $this->setTocPrefix($this->assertString($value, $name)),
                'toc_tag' => $this->setTocTag($this->assertString($value, $name)),
                'toc_id' => $this->setTocId($this->assertString($value, $name)),
                default => throw new \InvalidArgumentException(sprintf('Unknown option "%s".', $name)),
            };
        }
    }

    /**
     * @param list<string> $headingLevels
     */
    public function setHeadingLevels(array $headingLevels): void
    {
        foreach ($headingLevels as $level) {
            if (!in_array($level, self::ALLOWED_HEADING_LEVELS, true)) {
                throw new \InvalidArgumentException(sprintf('Invalid heading level "%s".', $level));
            }
        }

        $this->options['heading_levels'] = $headingLevels;
    }

    public function setDelimiter(string $slugDelimiter): void
    {
        if ($slugDelimiter === '') {
            throw new \InvalidArgumentException('Slug delimiter cannot be empty.');
        }

        $this->options['delimiter'] = $slugDelimiter;
    }

    public function setTocItemsLimit(?int $tocItemsLimit): void
    {
        if ($tocItemsLimit !== null && $tocItemsLimit < 0) {
            throw new \InvalidArgumentException('TOC item limit must be null or a non-negative integer.');
        }

        $this->options['toc_items_limit'] = $tocItemsLimit;
    }

    public function setLowercase(bool $slugLowercase): void
    {
        $this->options['lowercase'] = $slugLowercase;
    }

    /**
     * @param array<string, string>|null $slugReplacements
     */
    public function setReplacements(?array $slugReplacements): void
    {
        $this->options['replacements'] = $slugReplacements;
    }

    public function setTransliterate(bool $slugTransliterate): void
    {
        $this->options['transliterate'] = $slugTransliterate;
    }

    public function setUrlencode(bool $slugUrlencode): void
    {
        $this->options['urlencode'] = $slugUrlencode;
    }

    /**
     * @param list<string> $reservedIds
     */
    public function setReservedIds(array $reservedIds): void
    {
        $this->options['reserved_ids'] = $reservedIds;
    }

    public function setTocPrefix(string $prefix): void
    {
        $this->options['prefix'] = $prefix;
    }

    public function setTocTag(string $tocTag): void
    {
        if ($tocTag === '') {
            throw new \InvalidArgumentException('TOC tag cannot be empty.');
        }

        $this->options['toc_tag'] = $tocTag;
    }

    public function setTocId(string $tocId): void
    {
        if ($tocId === '') {
            throw new \InvalidArgumentException('TOC ID cannot be empty.');
        }

        $this->options['toc_id'] = $tocId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Parsedown override.
     *
     * Parameter is intentionally untyped to remain compatible with Parsedown.
     */
    /** @psalm-suppress MissingParamType */
    #[\Override]
    protected function blockHeader($Line): ?array
    {
        $block = parent::blockHeader($Line);

        return is_array($block) ? $this->processHeadingBlock($block) : null;
    }

    /**
     * Parsedown override.
     *
     * Parameter $Line is intentionally untyped to remain compatible with Parsedown.
     */

    /** @psalm-suppress MissingParamType */
    #[\Override]
    protected function blockSetextHeader($Line, ?array $Block = null): ?array
    {
        $block = parent::blockSetextHeader($Line, $Block);

        return is_array($block) ? $this->processHeadingBlock($block) : null;
    }

    public function body(string $text): string
    {
        $this->resetParserState();

        return $this->renderMarkdown($text);
    }

    /**
     * @return string|array<int, array{id: string, text: string, level: string}>
     */
    public function getContentsList(string $returnType = 'html'): string|array
    {
        return match (strtolower($returnType)) {
            'string', 'html' => $this->renderContentsListHtml(),
            'json' => $this->renderContentsListJson(),
            'array' => $this->contentsListArray,
            default => throw new \InvalidArgumentException(
                sprintf('Unknown TOC return type "%s". Expected html, string, json, or array.', $returnType)
            ),
        };
    }

    /**
     * Set custom anchor ID generation logic.
     *
     * The generator receives the heading text and current options and must return
     * the desired anchor ID string. The developer is fully responsible for slug
     * formatting, prefix, casing, and sanitization. ParsedownToc only guarantees
     * that the returned value will be made unique across the document.
     *
     * @param callable(string, array<string, mixed>): string $anchorIdGenerator
     */
    public function setAnchorIdGenerator(callable $anchorIdGenerator): void
    {
        $this->anchorIdGenerator = $anchorIdGenerator;
    }

    /**
     * Backwards-compatible alias for older integrations.
     *
     * @deprecated 2.0.0 Use setAnchorIdGenerator() instead.
     *
     * @param callable(string, array<string, mixed>): string $callback
     */
    public function setCreateAnchorIDCallback(callable $callback): void
    {
        @trigger_error(
            sprintf(
                '%s::setCreateAnchorIDCallback() is deprecated since parsedown-toc 2.0.0. Use %s::setAnchorIdGenerator() instead.',
                self::class,
                self::class
            ),
            E_USER_DEPRECATED
        );

        $this->setAnchorIdGenerator($callback);
    }

    /**
     * Parsedown override.
     *
     * Parameter is intentionally untyped to remain compatible with Parsedown.
     */
    /** @psalm-suppress MissingParamType */
    #[\Override]
    public function text($text): string
    {
        if (!is_string($text)) {
            throw new \TypeError(sprintf('%s::text() expects parameter 1 to be string.', self::class));
        }

        $this->resetParserState();

        $html = $this->renderMarkdown($text);
        $tocTag = $this->getTocTag();

        if (!str_contains($text, $tocTag)) {
            return $html;
        }

        $tocHtml = $this->renderContentsListHtml();
        $tocId = htmlspecialchars($this->getTocIdAttribute(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $needle = '<p>' . $tocTag . '</p>';
        $replacement = sprintf('<div id="%s">%s</div>', $tocId, $tocHtml);

        return str_replace($needle, $replacement, $html);
    }

    /**
     * @param array<string, mixed> $block
     * @return array<string, mixed>|null
     */
    private function processHeadingBlock(array $block): ?array
    {
        if ($block === []) {
            return null;
        }

        $element = $block['element'] ?? null;

        if (!is_array($element)) {
            return $block;
        }

        $level = $element['name'] ?? null;

        if (!is_string($level) || !in_array($level, $this->options['heading_levels'], true)) {
            return $block;
        }

        $text = $this->extractHeadingText($element);
        $attributes = $element['attributes'] ?? [];

        if (!is_array($attributes)) {
            $attributes = [];
        }

        $hasCustomId = isset($attributes['id']);
        $id = $hasCustomId
            ? $this->normalizeCustomAnchorId((string) $attributes['id'])
            : $this->createAnchorID($text);

        if ($hasCustomId) {
            $this->reserveAnchorID($id);
        }

        $attributes['id'] = $id;
        $block['element']['attributes'] = $attributes;

        $this->setContentsList([
            'text' => $text,
            'id' => $id,
            'level' => $level,
        ]);

        return $block;
    }

    /**
     * @param array<string, mixed> $element
     */
    private function extractHeadingText(array $element): string
    {
        $text = $element['text'] ?? $element['handler']['argument'] ?? '';

        return is_scalar($text) ? (string) $text : '';
    }

    private function renderContentsListHtml(): string
    {
        if ($this->contentsListArray === []) {
            return '';
        }

        $tree = [];
        $stack = [];

        foreach ($this->contentsListArray as $content) {
            $level = (int) trim($content['level'], 'h');

            $node = [
                'content' => $content,
                'children' => [],
            ];

            while ($stack !== [] && $stack[array_key_last($stack)]['level'] >= $level) {
                array_pop($stack);
            }

            if ($stack === []) {
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

    /**
     * @param array<int, array{content: array{id: string, text: string, level: string}, children: list}> $nodes
     */
    private function renderContentsListNodes(array $nodes): string
    {
        if ($nodes === []) {
            return '';
        }

        $html = '<ul>' . PHP_EOL;

        foreach ($nodes as $node) {
            $content = $node['content'];

            $text = $this->fetchText($content['text']);
            $id = $content['id'];
            $href = '#' . $id;

            $html .= sprintf(
                '<li><a href="%s">%s</a>',
                htmlspecialchars($href, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
            );

            if ($node['children'] !== []) {
                $html .= PHP_EOL . $this->renderContentsListNodes($node['children']);
            }

            $html .= '</li>' . PHP_EOL;
        }

        return $html . '</ul>' . PHP_EOL;
    }

    private function createAnchorID(string $text): string
    {
        if ($this->anchorIdGenerator !== null) {
            return (string) call_user_func($this->anchorIdGenerator, $text, $this->options);
        }

        $text = $this->normalizeString($text);

        if ($this->options['lowercase'] === true) {
            $text = mb_strtolower($text, 'UTF-8');
        }

        if (is_array($this->options['replacements'])) {
            $text = $this->applyReplacements($text, $this->options['replacements']);
        }

        if ($this->options['transliterate'] === true) {
            $text = $this->transliterateWithCharacterMap($text);

            if ($this->options['lowercase'] === true) {
                $text = strtolower($text);
            }
        }

        $text = $this->sanitizeAnchor($text);

        if ($this->options['urlencode'] === true) {
            $text = rawurlencode($text);
        }

        $text = $this->applyAnchorPrefix($text);

        return $this->finalizeAnchorID($text);
    }

    /**
     * @param array<string, string> $replacements
     */
    private function applyReplacements(string $text, array $replacements): string
    {
        foreach ($replacements as $search => $replacement) {
            if ($this->isRegexPattern($search)) {
                $result = preg_replace($search, $replacement, $text);

                if ($result === null) {
                    throw new \InvalidArgumentException(sprintf('Invalid replacement regex pattern "%s".', $search));
                }

                $text = $result;

                continue;
            }

            $text = str_replace($search, $replacement, $text);
        }

        return $text;
    }

    private function isRegexPattern(string $pattern): bool
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

    private function finalizeAnchorID(string $text): string
    {
        $text = trim($text);

        if ($text === '') {
            $text = 'section';
        }

        return $this->uniquifyAnchorID($text);
    }

    private function normalizeString(string $text): string
    {
        return $this->normalizeUnicode($text);
    }

    private function normalizeUnicode(string $text): string
    {
        if (class_exists('\Normalizer')) {
            $normalized = \Normalizer::normalize($text, \Normalizer::FORM_C);

            if (is_string($normalized)) {
                return $normalized;
            }
        }

        return mb_scrub($text, 'UTF-8');
    }

    private function transliterateWithCharacterMap(string $text): string
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
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'E', 'Ł' => 'L', 'Ń' => 'N', 'Ś' => 'S', 'Ź' => 'Z', 'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ś' => 's', 'ź' => 'z', 'ż' => 'z',

            // Latvian
            'Ā' => 'A', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'I', 'Ķ' => 'K', 'Ļ' => 'L', 'Ņ' => 'N', 'Ū' => 'U',
            'ā' => 'a', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n', 'ū' => 'u',
        ];

        return strtr($text, $characterMap);
    }

    private function sanitizeAnchor(string $text): string
    {
        $delimiter = (string) $this->options['delimiter'];

        if ($delimiter === '') {
            throw new \RuntimeException('Slug delimiter cannot be empty.');
        }

        $pattern = $this->options['transliterate'] === true
            ? '/[^A-Za-z0-9]+/'
            : '/[^\p{L}\p{Nd}]+/u';

        $text = $this->safePregReplace($pattern, $delimiter, $text);
        $text = $this->safePregReplace('/(' . preg_quote($delimiter, '/') . '){2,}/', '$1', $text);

        return trim($text, $delimiter);
    }

    private function applyAnchorPrefix(string $text): string
    {
        $prefix = (string) $this->options['prefix'];

        if ($prefix === '') {
            return $text;
        }

        return $prefix . $text;
    }

    private function uniquifyAnchorID(string $text): string
    {
        /** @var list<string> $reservedIds */
        $reservedIds = $this->options['reserved_ids'];

        $this->anchorDuplicates[$text] ??= 0;

        if (!in_array($text, $reservedIds, true) && $this->anchorDuplicates[$text] === 0) {
            $this->anchorDuplicates[$text] = 1;

            return $text;
        }

        $baseText = $text;
        $count = max(1, $this->anchorDuplicates[$baseText]);

        do {
            $text = sprintf('%s-%d', $baseText, $count);
            $count++;
        } while (in_array($text, $reservedIds, true) || isset($this->anchorDuplicates[$text]));

        $this->anchorDuplicates[$baseText] = $count;
        $this->anchorDuplicates[$text] = 1;

        return $text;
    }

    private function reserveAnchorID(string $id): void
    {
        $this->anchorDuplicates[$id] ??= 1;
    }

    private function resetParserState(): void
    {
        $this->anchorDuplicates = [];
        $this->contentsListArray = [];
    }

    private function renderMarkdown(string $text): string
    {
        $encodedText = $this->encodeTagToHash($text);
        $html = parent::text($encodedText);

        return $this->decodeTagFromHash($html);
    }

    private function decodeTagFromHash(string $text): string
    {
        $tocTag = $this->getTocTag();
        $hashedTag = $this->getHashedTocTag();

        if (!str_contains($text, $hashedTag)) {
            return $text;
        }

        return str_replace($hashedTag, $tocTag, $text);
    }

    private function encodeTagToHash(string $text): string
    {
        $tocTag = $this->getTocTag();

        if (!str_contains($text, $tocTag)) {
            return $text;
        }

        return str_replace($tocTag, $this->getHashedTocTag(), $text);
    }

    private function fetchText(string $text): string
    {
        return trim(strip_tags($this->line($text)));
    }

    private function getTocIdAttribute(): string
    {
        return (string) $this->options['toc_id'];
    }

    private function getSalt(): string
    {
        return $this->salt ??= sha1((string) microtime(true));
    }

    public function getTocTag(): string
    {
        return (string) $this->options['toc_tag'];
    }

    /**
     * @param array{id: string, text: string, level: string} $content
     */
    private function setContentsList(array $content): void
    {
        $limit = $this->options['toc_items_limit'];

        if ($limit !== null && count($this->contentsListArray) >= $limit) {
            return;
        }

        $this->contentsListArray[] = $content;
    }

    private function renderContentsListJson(): string
    {
        try {
            return json_encode($this->contentsListArray, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('Failed to encode table of contents as JSON.', 0, $exception);
        }
    }

    private function getHashedTocTag(): string
    {
        return hash('sha256', $this->getSalt() . $this->getTocTag());
    }

    private function normalizeCustomAnchorId(string $id): string
    {
        $id = $this->normalizeString($id);
        $id = preg_replace('/[\x00-\x1F\x7F]/u', '', $id);

        if ($id === null || trim($id) === '') {
            return $this->finalizeAnchorID('section');
        }

        return trim($id);
    }

    private function safePregReplace(string $pattern, string $replacement, string $subject): string
    {
        $result = preg_replace($pattern, $replacement, $subject);

        if ($result === null) {
            throw new \RuntimeException(sprintf('Regex replacement failed for pattern "%s".', $pattern));
        }

        return $result;
    }

    private function assertRuntimeRequirements(): void
    {
        if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<')) {
            throw new \RuntimeException(sprintf(
                'ParsedownToc requires PHP %s or later. Current version: %s.',
                self::MIN_PHP_VERSION,
                PHP_VERSION
            ));
        }

        if (version_compare(\Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED, '<')) {
            throw new \RuntimeException(sprintf(
                'ParsedownToc requires Parsedown %s or later. Current version: %s.',
                self::VERSION_PARSEDOWN_REQUIRED,
                \Parsedown::version
            ));
        }

        if (
            class_exists('ParsedownExtra')
            && version_compare(\ParsedownExtra::version, self::VERSION_PARSEDOWN_EXTRA_REQUIRED, '<')
        ) {
            throw new \RuntimeException(sprintf(
                'ParsedownToc requires ParsedownExtra %s or later. Current version: %s.',
                self::VERSION_PARSEDOWN_EXTRA_REQUIRED,
                \ParsedownExtra::version
            ));
        }
    }

    private function callParentConstructor(): void
    {
        $parentClass = get_parent_class($this);

        if (!is_string($parentClass) || !method_exists($parentClass, '__construct')) {
            return;
        }

        $constructor = new \ReflectionMethod($parentClass, '__construct');
        $constructor->invoke($this);
    }

    /**
     * @return list<string>
     */
    private function assertStringList(mixed $value, string $optionName): array
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Option "%s" must be an array of strings.', $optionName));
        }

        foreach ($value as $item) {
            if (!is_string($item)) {
                throw new \InvalidArgumentException(sprintf('Option "%s" must contain strings only.', $optionName));
            }
        }

        return array_values($value);
    }

    private function assertString(mixed $value, string $optionName): string
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException(sprintf('Option "%s" must be a string.', $optionName));
        }

        return $value;
    }

    private function assertBool(mixed $value, string $optionName): bool
    {
        if (!is_bool($value)) {
            throw new \InvalidArgumentException(sprintf('Option "%s" must be a boolean.', $optionName));
        }

        return $value;
    }

    private function assertNullableNonNegativeInt(mixed $value, string $optionName): ?int
    {
        if ($value === null) {
            return null;
        }

        if (!is_int($value) || $value < 0) {
            throw new \InvalidArgumentException(
                sprintf('Option "%s" must be null or a non-negative integer.', $optionName)
            );
        }

        return $value;
    }

    /**
     * @return array<string, string>|null
     */
    private function assertNullableStringMap(mixed $value, string $optionName): ?array
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Option "%s" must be null or an array.', $optionName));
        }

        $result = [];

        foreach ($value as $search => $replacement) {
            if (!is_string($search) || !is_scalar($replacement)) {
                throw new \InvalidArgumentException(
                    sprintf('Option "%s" must be an array<string, scalar>.', $optionName)
                );
            }

            $result[$search] = (string) $replacement;
        }

        return $result;
    }
}
