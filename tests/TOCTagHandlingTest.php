<?php

use PHPUnit\Framework\TestCase;

class TOCTagHandlingTest extends TestCase
{
    protected $parsedownToc;

    protected function setUp(): void
    {
        $this->parsedownToc = new ParsedownToc();
        $this->parsedownToc->setSafeMode(true);
    }

    public function testTOCTagReplacement()
    {
        $markdownWithTOC = "Some content\n\n[toc]\n\nMore content";
        $output = $this->parsedownToc->text($markdownWithTOC);
        // Check if $output contains the expected TOC div with the id set in options
        $this->assertStringContainsString('<div id="toc">', $output);
        // Further checks can verify the correctness of the TOC content itself
    }

    public function testCustomTOCTagReplacement()
    {
        $this->parsedownToc->setTocTag('[nav]');

        $output = $this->parsedownToc->text("# Heading\n\n[nav]");

        $this->assertStringContainsString('<div id="toc">', $output);
        $this->assertStringContainsString('<a href="#heading">Heading</a>', $output);
        $this->assertStringNotContainsString('<p>[nav]</p>', $output);
    }

    public function testCustomTOCIdIsUsedInReplacement()
    {
        $this->parsedownToc->setTocId('main-toc');

        $output = $this->parsedownToc->text("# Heading\n\n[toc]");

        $this->assertStringContainsString('<div id="main-toc">', $output);
    }

    public function testTOCIdIsEscapedInReplacement()
    {
        $this->parsedownToc->setTocId('toc" onclick="alert(1)');

        $output = $this->parsedownToc->text("# Heading\n\n[toc]");

        $this->assertStringContainsString('<div id="toc&quot; onclick=&quot;alert(1)">', $output);
        $this->assertStringNotContainsString('<div id="toc" onclick="alert(1)">', $output);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownToc);
    }
}
