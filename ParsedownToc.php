<?php

class ParsedownToc extends Parsedown {

    const VERSION = '1.0';

    public function __construct()
    {
        if (version_compare(parent::version, '0.7.1') < 0) {
            throw new Exception('Parsedown-toc requires a later version of Parsedown');
        }

        $this->BlockTypes['['][] = 'Toc';
    }

    private $fullDocument;

    protected function textElements($text)
    {
        // make sure no definitions are set
        $this->DefinitionData = array();

        // standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        // remove surrounding line breaks
        $text = trim($text, "\n");

        // Save a copy of the document
        $this->fullDoc = $text;

        // split text into lines
        $lines = explode("\n", $text);
        // iterate through lines to identify blocks
        return $this->linesElements($lines);
    }

    //
    // Header
    // -------------------------------------------------------------------------

    protected function blockHeader($Line)
    {
        $Block = parent::blockHeader($Line);
        if (preg_match('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];
            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);
            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        // createAnchorID
        if (!isset($Block['element']['attributes']['id']) && isset($Block['element']['handler']['argument'])) {
            $Block['element']['attributes']['id'] = $this->createAnchorID($Block['element']['handler']['argument'], ['transliterate' => true]);
        }

        $link = "#".$Block['element']['attributes']['id'];

        $Block['element']['handler']['argument'] = $Block['element']['handler']['argument']."<a class='heading-link' href='{$link}'> <i class='fas fa-link'></i></a>";

        // ~

        return $Block;
    }

    //
    // Setext
    protected function blockSetextHeader($Line, array $Block = null)
    {
        $Block = parent::blockSetextHeader($Line, $Block);
        if (preg_match('/[ ]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];
            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);
            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        // createAnchorID
        if (!isset($Block['element']['attributes']['id']) && isset($Block['element']['handler']['argument'])) {
            $Block['element']['attributes']['id'] = $this->createAnchorID($Block['element']['handler']['argument'], ['transliterate' => true]);
        }

        if($Block['type'] == 'Paragraph') {
            $link = "#".$Block['element']['attributes']['id'];
            $Block['element']['handler']['argument'] = $Block['element']['handler']['argument']."<a class='heading-link' href='{$link}'> <i class='fas fa-link'></i></a>";
        }

        // ~

        return $Block;
    }


    //
    // Toc
    // -------------------------------------------------------------------------

    protected $contentsListArray = array();
    protected $firstHeadLevel = 0;

    protected function blockToc($Line, array $Block = null)
    {
        if ($Line['text'] == '[toc]') {
            $headerLines = array();
            $prevLine = '';

            $cleanDoc = preg_replace('/<!--(.|\s)*?-->/', '', $this->fullDoc);

            // split text into lines
            $lines = explode("\n", $cleanDoc);

            foreach ($lines as $headerLine) {
                if (strspn($headerLine, '#') > 0 || strspn($headerLine, '=') > 3 || strspn($headerLine, '-') > 3) {
                    $level = strspn($headerLine, '#');

                    // Setext headers
                    if (strspn($headerLine, '=') > 3 && $prevLine !== '') {
                        $level = 1;
                        $headerLine = $prevLine;
                    } elseif (strspn($headerLine, '-') > 3 && $prevLine !== '') {
                        $level = 2;
                        $headerLine = $prevLine;
                    }

                    if ($level > 0 && $level <= 6) {
                        $text = preg_replace('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', '', $headerLine);
                        $text = trim(trim($text, '#'));

                        // createAnchorID
                        $id = $this->createAnchorID($text, ['transliterate' => true]);

                        if (preg_match('/{('.$this->regexAttribute.'+)}$/', $headerLine, $matches)) {
                            if (strspn($matches[1], '#') > 0) {
                                $id = trim($matches[1], '#');
                            }
                        }


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

                        $this->contentsListArray[] = "$indent- [${text}](#${id})\n";
                    }
                }
                $prevLine = $headerLine;
            }


            $Block = array(

                'element' => array(
                    'name' => 'nav',
                    'attributes' => array(
                        'id'   => 'table-of-contents',
                    ),
                    'elements' => array(
                        '1' => array(
                            "handler" => array(
                                "function" => "li",
                                "argument" => $this->contentsListArray,
                                "destination" => "elements",
                            ),
                        ),
                    ),
                ),
            );

            // ~

            return $Block;
        }
    }


    private function createAnchorID($str, $options = array())
    {
        // Make sure string is in UTF-8 and strip invalid UTF-8 characters
        $str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());

        $defaults = array(
            'delimiter' => '-',
            'limit' => null,
            'lowercase' => true,
            'replacements' => array(),
            'transliterate' => false,
        );

        // Merge options
        $options = array_merge($defaults, $options);

        $char_map = array(
            // Latin
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'Aa', 'Æ' => 'AE', 'Ç' => 'C',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ő' => 'O',
            'Ø' => 'Oe', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U', 'Ý' => 'Y', 'Þ' => 'TH',
            'ß' => 'ss', 'Œ' => 'OE',
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'aa', 'æ' => 'ae', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o',
            'ø' => 'oe', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th',
            'ÿ' => 'y', 'œ' => 'oe',
            // Latin symbols
            '©' => '(c)',
            // Greek
            'Α' => 'A', 'Β' => 'B', 'Γ' => 'G', 'Δ' => 'D', 'Ε' => 'E', 'Ζ' => 'Z', 'Η' => 'H', 'Θ' => '8',
            'Ι' => 'I', 'Κ' => 'K', 'Λ' => 'L', 'Μ' => 'M', 'Ν' => 'N', 'Ξ' => '3', 'Ο' => 'O', 'Π' => 'P',
            'Ρ' => 'R', 'Σ' => 'S', 'Τ' => 'T', 'Υ' => 'Y', 'Φ' => 'F', 'Χ' => 'X', 'Ψ' => 'PS', 'Ω' => 'W',
            'Ά' => 'A', 'Έ' => 'E', 'Ί' => 'I', 'Ό' => 'O', 'Ύ' => 'Y', 'Ή' => 'H', 'Ώ' => 'W', 'Ϊ' => 'I',
            'Ϋ' => 'Y',
            'α' => 'a', 'β' => 'b', 'γ' => 'g', 'δ' => 'd', 'ε' => 'e', 'ζ' => 'z', 'η' => 'h', 'θ' => '8',
            'ι' => 'i', 'κ' => 'k', 'λ' => 'l', 'μ' => 'm', 'ν' => 'n', 'ξ' => '3', 'ο' => 'o', 'π' => 'p',
            'ρ' => 'r', 'σ' => 's', 'τ' => 't', 'υ' => 'y', 'φ' => 'f', 'χ' => 'x', 'ψ' => 'ps', 'ω' => 'w',
            'ά' => 'a', 'έ' => 'e', 'ί' => 'i', 'ό' => 'o', 'ύ' => 'y', 'ή' => 'h', 'ώ' => 'w', 'ς' => 's',
            'ϊ' => 'i', 'ΰ' => 'y', 'ϋ' => 'y', 'ΐ' => 'i',
            // Turkish
            'Ş' => 'S', 'İ' => 'I', 'Ç' => 'C', 'Ü' => 'U', 'Ö' => 'O', 'Ğ' => 'G',
            'ş' => 's', 'ı' => 'i', 'ç' => 'c', 'ü' => 'u', 'ö' => 'o', 'ğ' => 'g',
            // Russian
            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh',
            'З' => 'Z', 'И' => 'I', 'Й' => 'J', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
            'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
            'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sh', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu',
            'Я' => 'Ya',
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh',
            'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
            'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c',
            'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sh', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu',
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
            'Ą' => 'A', 'Ć' => 'C', 'Ę' => 'e', 'Ł' => 'L', 'Ń' => 'N', 'Ó' => 'o', 'Ś' => 'S', 'Ź' => 'Z',
            'Ż' => 'Z',
            'ą' => 'a', 'ć' => 'c', 'ę' => 'e', 'ł' => 'l', 'ń' => 'n', 'ó' => 'o', 'ś' => 's', 'ź' => 'z',
            'ż' => 'z',
            // Latvian
            'Ā' => 'A', 'Č' => 'C', 'Ē' => 'E', 'Ģ' => 'G', 'Ī' => 'i', 'Ķ' => 'k', 'Ļ' => 'L', 'Ņ' => 'N',
            'Š' => 'S', 'Ū' => 'u', 'Ž' => 'Z',
            'ā' => 'a', 'č' => 'c', 'ē' => 'e', 'ģ' => 'g', 'ī' => 'i', 'ķ' => 'k', 'ļ' => 'l', 'ņ' => 'n',
            'š' => 's', 'ū' => 'u', 'ž' => 'z'
        );

        // Make custom replacements
        $str = preg_replace(array_keys($options['replacements']), $options['replacements'], $str);

        // Transliterate characters to ASCII
        if ($options['transliterate']) {
            $str = str_replace(array_keys($char_map), $char_map, $str);
        }

        // Replace non-alphanumeric characters with our delimiter
        $str = preg_replace('/[^\p{L}\p{Nd}]+/u', $options['delimiter'], $str);

        // Remove duplicate delimiters
        $str = preg_replace('/(' . preg_quote($options['delimiter'], '/') . '){2,}/', '$1', $str);

        // Truncate slug to max. characters
        $str = mb_substr($str, 0, ($options['limit'] ? $options['limit'] : mb_strlen($str, 'UTF-8')), 'UTF-8');

        // Remove delimiter from ends
        $str = trim($str, $options['delimiter']);

        return $options['lowercase'] ? mb_strtolower($str, 'UTF-8') : $str;
    }

    protected function parseAttributeData($attributeString)
    {
        $Data = array();

        $attributes = preg_split('/[ ]+/', $attributeString, - 1, PREG_SPLIT_NO_EMPTY);

        foreach ($attributes as $attribute)
        {
            if ($attribute[0] === '#')
            {
                $Data['id'] = substr($attribute, 1);
            }
            else // "."
            {
                $classes []= substr($attribute, 1);
            }
        }

        if (isset($classes))
        {
            $Data['class'] = implode(' ', $classes);
        }

        return $Data;
    }

    protected $regexAttribute = '(?:[#.][-\w]+[ ]*)';
}

?>
