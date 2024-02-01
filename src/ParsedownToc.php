<?php
/**
 * 
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
    const VERSION = '1.6.0';
    const VERSION_PARSEDOWN_REQUIRED = '1.7.4';
    const VERSION_PARSEDOWN_EXTRA_REQUIRED = '0.8.1';

    private $createAnchorIDCallback = null;
    
    protected $options = [];
    protected $defaultOptions = array(
        'selectors' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        'delimiter' => '-',
        'limit' => null,
        'lowercase' => true,
        'replacements' => null,
        'transliterate' => true,
        'urlencode' => false,
        'blacklist' => [],
        'url' => '',
        'toc_tag' => '[toc]',
        'toc_id' => 'toc',
    );


    public function __construct()
    {
        if (version_compare(\Parsedown::version, self::VERSION_PARSEDOWN_REQUIRED) < 0) {
            $msg_error  = 'Version Error.' . PHP_EOL;
            $msg_error .= '  ParsedownToc requires a later version of Parsedown.' . PHP_EOL;
            $msg_error .= '  - Current version : ' . \Parsedown::version . PHP_EOL;
            $msg_error .= '  - Required version: ' . self::VERSION_PARSEDOWN_REQUIRED .' and later'. PHP_EOL;
            throw new Exception($msg_error);
        }

        # If ParsedownExtra is installed, check its version
        if (class_exists('ParsedownExtra')) {
            if (version_compare(\ParsedownExtra::version, self::VERSION_PARSEDOWN_EXTRA_REQUIRED) < 0) {
                $msg_error  = 'Version Error.' . PHP_EOL;
                $msg_error .= '  ParsedownToc requires a later version of ParsedownExtra.' . PHP_EOL;
                $msg_error .= '  - Current version : ' . \ParsedownExtra::version . PHP_EOL;
                $msg_error .= '  - Required version: ' . self::VERSION_PARSEDOWN_EXTRA_REQUIRED .' and later'. PHP_EOL;
                throw new Exception($msg_error);
            }
        }

        if (is_callable('parent::__construct')) {
            parent::__construct();
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
    public function setOptions(array $options) : void
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Set the selectors option.
     *
     * @param array $selectors The selectors to set.
     * @return void
     */
    public function setTocSelectors(array $selectors) : void
    {
        $this->options['selectors'] = $selectors;
    }

    /**
     * Set the delimiter option.
     *
     * @param string $delimiter The delimiter to set.
     * @return void
     */
    public function setTocDelimiter(string $delimiter) : void
    {
        $this->options['delimiter'] = $delimiter;
    }

    /**
     * Set the limit option.
     *
     * @param int|null $limit The limit to set.
     * @return void
     */
    public function setTocLimit(?int $limit) : void
    {
        $this->options['limit'] = $limit;
    }

    /**
     * Set the lowercase option.
     *
     * @param bool $lowercase The lowercase option to set.
     * @return void
     */
    public function setTocLowercase(bool $lowercase) : void
    {
        $this->options['lowercase'] = $lowercase;
    }

    /**
     * Set the replacements option.
     *
     * @param array|null $replacements The replacements to set.
     * @return void
     */
    public function setTocReplacements(?array $replacements) : void
    {
        $this->options['replacements'] = $replacements;
    }

    /**
     * Set the transliterate option.
     *
     * @param bool $transliterate The transliterate option to set.
     * @return void
     */
    public function setTocTransliterate(bool $transliterate) : void
    {
        $this->options['transliterate'] = $transliterate;
    }

    /**
     * Set the urlencode option.
     *
     * @param bool $urlencode The urlencode option to set.
     * @return void
     */
    public function setTocUrlencode(bool $urlencode) : void
    {
        $this->options['urlencode'] = $urlencode;
    }

    /**
     * Set the blacklist option.
     *
     * @param array $blacklist The blacklist to set.
     * @return void
     */
    public function setTocBlacklist(array $blacklist) : void
    {
        $this->options['blacklist'] = $blacklist;
    }

    /**
     * Set the url option.
     *
     * @param string $url The url to set.
     * @return void
     */
    public function setTocUrl(string $url) : void
    {
        $this->options['url'] = $url;
    }

    /**
     * Set the toc_tag option.
     *
     * @param string $toc_tag The toc_tag to set.
     * @return void
     */
    public function setTocTag(string $toc_tag) : void
    {
        $this->options['toc_tag'] = $toc_tag;
    }

    /**
     * Set the toc_id option.
     *
     * @param string $toc_id The toc_id to set.
     * @return void
     */
    public function setTocId(string $toc_id) : void
    {
        $this->options['toc_id'] = $toc_id;
    }



    **
     * Heading process.
     * Creates heading block element and stores to the ToC list. It overrides
     * the parent method: \Parsedown::blockHeader() and returns $Block array if
     * the $Line is a heading element.
     *
     * @param  array $Line Array that Parsedown detected as a block type element.
     * @return void|array   Array of Heading Block.
     */
    protected function blockHeader($Line)
    {
        $Block = parent::blockHeader($Line);

        if (! empty($Block)) {
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];
            $id = $Block['element']['attributes']['id'] ?? $this->createAnchorID($text);

            $Block['element']['attributes'] = ['id' => $id];
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
    protected function blockSetextHeader($Line, $Block = null)
    {
        if (!$this->getSetting('headings')) {
            return;
        }

        $Block = parent::blockSetextHeader($Line, $Block);

        if (! empty($Block)) {
            $text = $Block['element']['text'] ?? $Block['element']['handler']['argument'] ?? '';
            $level = $Block['element']['name'];
            $id = $Block['element']['attributes']['id'] ?? $this->createAnchorID($text);

            $Block['element']['attributes'] = ['id' => $id];
            $this->setContentsList(['text' => $text, 'id' => $id, 'level' => $level]);

            return $Block;
        }
    }

    /**
     * Parses the given markdown string to an HTML string but it leaves the ToC
     * tag as is. It's an alias of the parent method "\parent::text()".
     *
     * @param  string $text  Markdown string to be parsed.
     * @return string        Parsed HTML string.
     */
    public function body($text) : string
    {
        $text = $this->encodeTagToHash($text);   // Escapes ToC tag temporary
        $html = parent::text($text);      // Parses the markdown text
        $html = $this->decodeTagFromHash($html); // Unescape the ToC tag

        return $html;
    }

    /**
     * Returns the contents list in the specified format.
     *
     * @param string $type_return The format of the contents list to return. Default is 'html'.
     *                            Possible values are 'string', 'html', 'json', and 'array'.
     * @return string|array       The contents list in the specified format.
     * @throws InvalidArgumentException If an unknown return type is given.
     */
    public function contentsList($type_return = 'html'): string
    {
        switch (strtolower($type_return)) {
            case 'string': // for backward compatibility
            case 'html':
                return $this->contentsListString ? $this->body($this->contentsListString) : '';
            case 'json':
                return json_encode($this->contentsListArray);
            case 'array':
                return $this->contentsListArray;
            default:
                $backtrace = debug_backtrace();
                $caller = $backtrace[0];
                $errorMessage = "Unknown return type '{$type_return}' given while parsing ToC. Called in " . $caller['file'] . " on line " . $caller['line'];
                throw new InvalidArgumentException($errorMessage);
        }
    }

    /**
     * Allows users to define their own logic for createAnchorID.
     */
    public function setCreateAnchorIDCallback(callable $callback): void
    {
        $this->createAnchorIDCallback = $callback;
    }

    /**
     * Generates an anchor text that are link-able even the heading is not in
     * ASCII.
     *
     * @param  string $text
     * @return string
     */
    protected function createAnchorID($str) : string
    {
        // Use user-defined logic if a callback is provided
        if (is_callable($this->createAnchorIDCallback)) {
            return call_user_func($this->createAnchorIDCallback, $text, $this->getSettings());
        }
        
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        if($this->options['urlencode']) {
            // Check AnchorID is unique
            $str = $this->incrementAnchorId($str);

            return urlencode($str);
        }

        // Make custom replacements
        if(!empty($this->options['replacements'])) {
            $str = preg_replace(array_keys($this->options['replacements']), $this->options['replacements'], $str);
        }
        
        // Transliterate characters to ASCII
        if ($this->options['transliterate']) {
            $str = iconv('UTF-8', 'ASCII//IGNORE', $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $this->options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($this->options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($this->options['limit'] ? $this->options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $this->options['delimiter']);

        $str = $this->options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;

        $str = $this->incrementAnchorId($str);

        return $str;
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
    protected function decodeTagFromHash(string $text) : string
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTocTag();
        $tag_hashed = hash('sha256', $salt . $tag_origin);

        if (strpos($text, $tag_hashed) === false) {
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
    protected function encodeTagToHash(string $text) : string
    {
        $salt = $this->getSalt();
        $tag_origin = $this->getTocTag();

        if (strpos($text, $tag_origin) === false) {
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
    protected function fetchText(string $text) : string
    {
        return trim(strip_tags($this->line($text)));
    }

    /**
     * Gets the ID attribute of the ToC for HTML tags.
     *
     * @return string
     */
    protected function getTocIdAttribute() : string
    {
        return $this->options['toc_id'];
    }

    /**
     * Unique string to use as a salt value.
     *
     * @return string
     */
    protected function getSalt() : string
    {
        static $salt;
        if (isset($salt)) {
            return $salt;
        }

        $salt = hash('md5', time());
        return $salt;
    }

    /**
     * Gets the markdown tag for ToC.
     *
     * @return string
     */
    protected function getTocTag() : string
    {
        return $this->options['toc_tag'];
    }

    /**
     * Set/stores the heading block to ToC list in a string and array format.
     *
     * @param  array $Content   Heading info such as "level","id" and "text".
     * @return void
     */
    protected function setContentsList(array $Content) : void
    {
        // Stores as an array
        $this->setContentsListAsArray($Content);
        // Stores as string in markdown list format.
        $this->setContentsListAsString($Content);
    }

    /**
     * Sets/stores the heading block info as an array.
     *
     * @param  array $Content
     * @return void
     */
    protected function setContentsListAsArray(array $Content) : void
    {
        $this->contentsListArray[] = $Content;
    }

    protected $contentsListArray = array();

    /**
     * Sets/stores the heading block info as a list in markdown format.
     *
     * @param  array $Content  Heading info such as "level","id" and "text".
     * @return void
     */
    protected function setContentsListAsString(array $Content) : void
    {
        $text  = $this->fetchText($Content['text']);
        $id    = $Content['id'];
        $level = (integer) trim($Content['level'], 'h');
        $link  = "[{$text}]({$this->options['url']}#{$id})";

        if ($this->firstHeadLevel === 0) {
            $this->firstHeadLevel = $level;
        }
        $cutIndent = $this->firstHeadLevel - 1;
        if ($cutIndent > $level) {
            $level = 1;
        } else {
            $level = $level - $cutIndent;
        }

        $indent = str_repeat('  ', $level);

        // Stores in markdown list format as below:
        // - [Header1](#Header1)
        //   - [Header2-1](#Header2-1)
        //     - [Header3](#Header3)
        //   - [Header2-2](#Header2-2)
        // ...
        $this->contentsListString .= "{$indent}- {$link}" . PHP_EOL;
    }

    protected $contentsListString = '';
    protected $firstHeadLevel = 0;

    /**
     * Parses markdown string to HTML and also the "[toc]" tag as well.
     * It overrides the parent method: \Parsedown::text().
     *
     * @param  string $text
     * @return string
     */
    public function text($text) : string
    {
        // Parses the markdown text except the ToC tag. This also searches
        // the list of contents and available to get from "contentsList()"
        // method.
        $html = $this->body($text);

        $tag_origin  = $this->getTocTag();

        if (strpos($text, $tag_origin) === false) {
            return $html;
        }

        $data = $this->contentsList();
        $toc_id   = $this->getTocIdAttribute();
        $needle  = '<p>' . $tag_origin . '</p>';
        $replace = "<div id=\"{$toc_id}\">{$data}</div>";

        return str_replace($needle, $replace, $html);
    }


    protected $isBlacklistInitialized = false;
    protected $anchorDuplicates = [];

    /**
     * Add blacklisted ids to anchor list
     */
    protected function initBlacklist() : void
    {

        if ($this->isBlacklistInitialized) return;

        if (!empty($this->options['blacklist']) && is_array($this->options['blacklist'])) {

            foreach ($this->options['blacklist'] as $v) {
                if (is_string($v)) $this->anchorDuplicates[$v] = 0;
            }
        }

        $this->isBlacklistInitialized = true;
    }

    /**
     * Collect and count anchors in use to prevent duplicated ids. Return string
     * with incremental, numeric suffix. Also init optional blacklist of ids.
     *
     * @param  string $str
     * @return string
     */
    protected function incrementAnchorId(string $str) : string
    {

        // add blacklist to list of used anchors
        if (!$this->isBlacklistInitialized) $this->initBlacklist();

        $this->anchorDuplicates[$str] = !isset($this->anchorDuplicates[$str]) ? 0 : ++$this->anchorDuplicates[$str];

        $newStr = $str;

        if ($count = $this->anchorDuplicates[$str]) {

            $newStr .= "-{$count}";

            // increment until conversion doesn't produce new duplicates anymore
            if (isset($this->anchorDuplicates[$newStr])) {
                $newStr = $this->incrementAnchorId($str);
            }
            else {
                $this->anchorDuplicates[$newStr] = 0;
            }

        }

        return $newStr;
    }

}
