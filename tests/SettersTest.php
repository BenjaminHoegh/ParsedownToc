<?php

use PHPUnit\Framework\TestCase;

class SettersTest extends TestCase
{
    protected ParsedownToc $parsedownToc;

    protected function setUp(): void
    {
        $this->parsedownToc = new ParsedownToc();
        $this->parsedownToc->setSafeMode(true);
    }

    // -------------------------------------------------------------------------
    // Behavioral setter tests
    // -------------------------------------------------------------------------

    public function testSetHeadingLevels(): void
    {
        $this->parsedownToc->setHeadingLevels(['h1', 'h2', 'h3', 'h4']);

        $this->parsedownToc->text("# H1\n\n##### H5");
        $list = $this->parsedownToc->getContentsList('array');

        $levels = array_column($list, 'level');
        $this->assertContains('h1', $levels);
        $this->assertNotContains('h5', $levels);
    }

    public function testSetDelimiter(): void
    {
        $this->parsedownToc->setDelimiter('_');

        $output = $this->parsedownToc->text('# Hello World');

        $this->assertStringContainsString('id="hello_world"', $output);
    }

    public function testSetLowercase(): void
    {
        $this->parsedownToc->setLowercase(false);

        $output = $this->parsedownToc->text('# Hello World');

        $this->assertStringContainsString('id="Hello-World"', $output);
    }

    public function testSetReplacements(): void
    {
        $this->parsedownToc->setReplacements(['o' => '0']);

        $output = $this->parsedownToc->text('# Hello World');

        $this->assertStringContainsString('id="hell0-w0rld"', $output);
    }

    public function testSetTransliterate(): void
    {
        $this->parsedownToc->setTransliterate(true);

        $output = $this->parsedownToc->text('# Héllo');

        $this->assertStringContainsString('id="hello"', $output);
    }

    public function testSetUrlencode(): void
    {
        $this->parsedownToc->setLowercase(false)->setUrlencode(true);

        $output = $this->parsedownToc->text('# Héllo');

        $this->assertStringContainsString('id="H%C3%A9llo"', $output);
    }

    public function testSetReservedIds(): void
    {
        $this->parsedownToc->setReservedIds(['test']);

        $output = $this->parsedownToc->text('# Test');

        $this->assertStringContainsString('id="test-1"', $output);
    }

    public function testSetPrefix(): void
    {
        $this->parsedownToc->setPrefix('/docs/');

        $output = $this->parsedownToc->text('# Page');

        $this->assertStringContainsString('id="/docs/page"', $output);
    }

    public function testSetTocTag(): void
    {
        $this->parsedownToc->setTocTag('[nav]');

        $output = $this->parsedownToc->text("# Heading\n\n[nav]");

        $this->assertStringContainsString('<div id="toc">', $output);
        $this->assertStringNotContainsString('<p>[nav]</p>', $output);
    }

    public function testSetTocId(): void
    {
        $this->parsedownToc->setTocId('my-toc');

        $output = $this->parsedownToc->text("# Heading\n\n[toc]");

        $this->assertStringContainsString('<div id="my-toc">', $output);
    }

    public function testSetReplacementsAcceptsNull(): void
    {
        $this->parsedownToc->setReplacements(null);

        $output = $this->parsedownToc->text('# Test');

        $this->assertStringContainsString('id="test"', $output);
    }

    // -------------------------------------------------------------------------
    // Fluent chaining
    // -------------------------------------------------------------------------

    public function testFluentChainingReturnsSelf(): void
    {
        $result = $this->parsedownToc->setDelimiter('_');

        $this->assertInstanceOf(ParsedownToc::class, $result);
    }

    public function testFluentChainingAppliesAllSettings(): void
    {
        $output = $this->parsedownToc
            ->setDelimiter('_')
            ->setLowercase(false)
            ->text('# Hello World');

        $this->assertStringContainsString('id="Hello_World"', $output);
    }

    // -------------------------------------------------------------------------
    // Validation / guard-clause tests
    // -------------------------------------------------------------------------

    public function testSetHeadingLevelsThrowsOnInvalidLevel(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->parsedownToc->setHeadingLevels(['h7']);
    }

    public function testSetDelimiterThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->parsedownToc->setDelimiter('');
    }

    public function testSetTocTagThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->parsedownToc->setTocTag('');
    }

    public function testSetTocIdThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->parsedownToc->setTocId('');
    }

    // -------------------------------------------------------------------------

    protected function tearDown(): void
    {
        unset($this->parsedownToc);
    }
}

