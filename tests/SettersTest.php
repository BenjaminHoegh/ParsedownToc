<?php

use PHPUnit\Framework\TestCase;

class SettersTest extends TestCase
{
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
     * Test case for the `setTocSelectors` method.
     */
    public function testSetTocSelectors() 
    {
        $selectors = [
            'h1' => 'h1',
            'h2' => 'h2',
            'h3' => 'h3',
            'h4' => 'h4',
        ];

        $this->parsedownToc->setTocSelectors($selectors);
        $this->assertEquals($selectors, $this->parsedownToc->getOptions()['selectors']);
    }

    /**
     * Test case for the `setTocDelimiter` method.
     */
    public function testSetTocDelimiter()
    {
        $delimiter = '&';

        $this->parsedownToc->setTocDelimiter($delimiter);
        $this->assertEquals($delimiter, $this->parsedownToc->getOptions()['delimiter']);
    }

    /**
     * Test case for the `setTocLimit` method.
     */
    public function testSetTocLimit()
    {
        $limit = 3;

        $this->parsedownToc->setTocLimit($limit);
        $this->assertEquals($limit, $this->parsedownToc->getOptions()['limit']);
    }

    /**
     * Test case for the `setTocLowercase` method.
     */
    public function testSetTocLowercase()
    {
        $lowercase = false;

        $this->parsedownToc->setTocLowercase($lowercase);
        $this->assertEquals($lowercase, $this->parsedownToc->getOptions()['lowercase']);
    }

    /**
     * Test case for the `setTocReplacements` method.
     */
    public function testSetTocReplacements()
    {
        $replacements = [
            'BadKitty' => '-',
        ];

        $this->parsedownToc->setTocReplacements($replacements);
        $this->assertEquals($replacements, $this->parsedownToc->getOptions()['replacements']);
    }

    /**
     * Test case for the `setTocTransliterate` method.
     */
    public function testSetTocTransliterate()
    {
        $transliterate = false;

        $this->parsedownToc->setTocTransliterate($transliterate);
        $this->assertEquals($transliterate, $this->parsedownToc->getOptions()['transliterate']);
    }

    /**
     * Test case for the `setTocUrlencode` method.
     */
    public function testSetTocUrlencode()
    {
        $urlencode = false;

        $this->parsedownToc->setTocUrlencode($urlencode);
        $this->assertEquals($urlencode, $this->parsedownToc->getOptions()['urlencode']);
    }

    /**
     * Test case for the `setTocBlacklist` method.
     */
    public function testSetTocBlacklist()
    {
        $blacklist = [
            'myBlacklistedHeaderId',
        ];

        $this->parsedownToc->setTocBlacklist($blacklist);
        $this->assertEquals($blacklist, $this->parsedownToc->getOptions()['blacklist']);
    }

    /**
     * Test case for the `setTocPrefix` method.
     */
    public function testSetTocPrefix()
    {
        $prefix = 'toc';

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