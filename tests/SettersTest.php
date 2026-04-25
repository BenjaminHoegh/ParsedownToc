<?php

use PHPUnit\Framework\TestCase;

class SettersTest extends TestCase
{
    protected $parsedownToc;
    
    protected function setUp(): void
    {
        $this->parsedownToc = new ParsedownToc();
        $this->parsedownToc->setSafeMode(true);
    }

    /**
     * Test case for the `setOptions` method.
     */
    public function testSetOptions()
    {
        $options = [
            'toc_id' => '[[toc]]',
        ];

        $this->parsedownToc->setOptions($options);
        $this->assertEquals($options['toc_id'], $this->parsedownToc->getOptions()['toc_id']);
    }

    /**
     * Test case for the `setHeadingLevels` method.
     */
    public function testSetHeadingLevels() 
    {
        $headingLevels = [
            'h1' => 'h1',
            'h2' => 'h2',
            'h3' => 'h3',
            'h4' => 'h4',
        ];

        $this->parsedownToc->setHeadingLevels($headingLevels);
        $this->assertEquals($headingLevels, $this->parsedownToc->getOptions()['heading_levels']);
    }

    /**
     * Test case for the `setSlugDelimiter` method.
     */
    public function testSetSlugDelimiter()
    {
        $delimiter = '&';

        $this->parsedownToc->setSlugDelimiter($delimiter);
        $this->assertEquals($delimiter, $this->parsedownToc->getOptions()['slug_delimiter']);
    }

    /**
     * Test case for the `setTocItemsLimit` method.
     */
    public function testSetTocItemsLimit()
    {
        $limit = 3;

        $this->parsedownToc->setTocItemsLimit($limit);
        $this->assertEquals($limit, $this->parsedownToc->getOptions()['toc_items_limit']);
    }

    /**
     * Test case for the `setSlugLowercase` method.
     */
    public function testSetSlugLowercase()
    {
        $lowercase = false;

        $this->parsedownToc->setSlugLowercase($lowercase);
        $this->assertEquals($lowercase, $this->parsedownToc->getOptions()['slug_lowercase']);
    }

    /**
     * Test case for the `setSlugReplacements` method.
     */
    public function testSetSlugReplacements()
    {
        $replacements = [
            'BadKitty' => '-',
        ];

        $this->parsedownToc->setSlugReplacements($replacements);
        $this->assertEquals($replacements, $this->parsedownToc->getOptions()['slug_replacements']);
    }

    /**
     * Test case for the `setSlugTransliterate` method.
     */
    public function testSetSlugTransliterate()
    {
        $transliterate = false;

        $this->parsedownToc->setSlugTransliterate($transliterate);
        $this->assertEquals($transliterate, $this->parsedownToc->getOptions()['slug_transliterate']);
    }

    /**
     * Test case for the `setSlugUrlencode` method.
     */
    public function testSetSlugUrlencode()
    {
        $urlencode = false;

        $this->parsedownToc->setSlugUrlencode($urlencode);
        $this->assertEquals($urlencode, $this->parsedownToc->getOptions()['slug_urlencode']);
    }

    /**
     * Test case for the `setReservedIds` method.
     */
    public function testSetReservedIds()
    {
        $reservedIds = [
            'myBlacklistedHeaderId',
        ];

        $this->parsedownToc->setReservedIds($reservedIds);
        $this->assertEquals($reservedIds, $this->parsedownToc->getOptions()['reserved_ids']);
    }

    /**
     * Test case for the `setTocPrefix` method.
     */
    public function testSetTocPrefix()
    {
        $prefix = '/docs/page';

        $this->parsedownToc->setTocPrefix($prefix);
        $this->assertEquals($prefix, $this->parsedownToc->getOptions()['prefix']);
    }

    /**
     * Test case for the `setTocTag` method.
     */
    public function testSetTocTag()
    {
        $tag = 'nav';

        $this->parsedownToc->setTocTag($tag);
        $this->assertEquals($tag, $this->parsedownToc->getOptions()['toc_tag']);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownToc);
    }
}