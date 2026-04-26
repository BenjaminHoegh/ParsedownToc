<?php
use PHPUnit\Framework\TestCase;

class ContentListManagementTest extends TestCase
{
    protected $parsedownToc;

    protected function setUp(): void
    {
        $this->parsedownToc = new ParsedownToc();
        $this->parsedownToc->setSafeMode(true);
    }

    
    public function testContentsListString()
    {
        $markdown = "Some content\n\n# Heading 1\n\n## Heading 1.1\n\n# Heading 2\n\n## Heading 2.1";
        $this->parsedownToc->text($markdown); // Process markdown to generate TOC
        $result = $this->parsedownToc->contentsList('string');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        
        // Also check that we can use html as an alias for string
        $result = $this->parsedownToc->contentsList('html');
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testContentsListReturnsJson()
    {
        $markdown = "Some content\n\n# Heading 1\n\n## Heading 1.1\n\n# Heading 2\n\n## Heading 2.1";
        $this->parsedownToc->text($markdown); // Process markdown to generate TOC
        $result = $this->parsedownToc->contentsList('json');
        $this->assertIsString($result);
        $this->assertJson($result);
    }

    public function testContentsListJsonThrowsOnEncodingFailure()
    {
        $reflection = new ReflectionClass($this->parsedownToc);
        $property = $reflection->getProperty('contentsListArray');
        $property->setAccessible(true);
        $property->setValue($this->parsedownToc, [[
            'text' => "\xB1\x31",
            'id' => 'broken',
            'level' => 'h1',
        ]]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to encode table of contents as JSON.');

        $this->parsedownToc->contentsList('json');
    }
    
    public function testContentsListArray()
    {
        $markdown = "Some content\n\n# Heading 1\n\n## Heading 1.1\n\n# Heading 2\n\n## Heading 2.1";
        $this->parsedownToc->text($markdown); // Process markdown to generate TOC
        $result = $this->parsedownToc->contentsList('array');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function testContentsListReturnsHtmlByDefault()
    {
        $markdown = "Some content\n\n# Heading 1\n\n## Heading 1.1\n\n# Heading 2\n\n## Heading 2.1";
        $this->parsedownToc->text($markdown); // Process markdown to generate TOC
        $result = $this->parsedownToc->contentsList();
        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function testContentsListRespectsHeadingIdPrefix()
    {
        $this->parsedownToc->setTocPrefix('md-');

        $html = $this->parsedownToc->text("# Heading 1");
        $result = $this->parsedownToc->contentsList();

        $this->assertStringContainsString('<h1 id="md-heading-1">Heading 1</h1>', $html);
        $this->assertStringContainsString('<a href="#md-heading-1">Heading 1</a>', $result);
    }

    public function testContentsListRespectsLimit()
    {
        $this->parsedownToc->setTocItemsLimit(1);

        $html = $this->parsedownToc->text("# First\n\n# Second\n\n[toc]");
        $result = $this->parsedownToc->contentsList();

        $this->assertStringContainsString('<h1 id="first">First</h1>', $html);
        $this->assertStringContainsString('<h1 id="second">Second</h1>', $html);
        $this->assertStringContainsString('<a href="#first">First</a>', $result);
        $this->assertStringNotContainsString('<a href="#second">Second</a>', $result);
    }

    public function testContentsListInvalidType()
    {
        $markdown = "Some content\n\n# Heading 1\n\n## Heading 1.1\n\n# Heading 2\n\n## Heading 2.1";
        $this->parsedownToc->text($markdown); // Process markdown to generate TOC
        $this->expectException(InvalidArgumentException::class);
        $this->parsedownToc->contentsList('invalid');
    }

    public function testTextResetsParserStateBetweenDocuments()
    {
        $firstHtml = $this->parsedownToc->text("# Heading");
        $firstContents = $this->parsedownToc->contentsList('array');

        $secondHtml = $this->parsedownToc->text("# Heading");
        $secondContents = $this->parsedownToc->contentsList('array');

        $this->assertStringContainsString('<h1 id="heading">Heading</h1>', $firstHtml);
        $this->assertStringContainsString('<h1 id="heading">Heading</h1>', $secondHtml);
        $this->assertSame([['text' => 'Heading', 'id' => 'heading', 'level' => 'h1']], $firstContents);
        $this->assertSame([['text' => 'Heading', 'id' => 'heading', 'level' => 'h1']], $secondContents);
    }

    public function testBodyResetsParserStateBetweenDocuments()
    {
        $firstHtml = $this->parsedownToc->body("# Heading");
        $firstContents = $this->parsedownToc->contentsList('array');

        $secondHtml = $this->parsedownToc->body("# Heading");
        $secondContents = $this->parsedownToc->contentsList('array');

        $this->assertStringContainsString('<h1 id="heading">Heading</h1>', $firstHtml);
        $this->assertStringContainsString('<h1 id="heading">Heading</h1>', $secondHtml);
        $this->assertSame([['text' => 'Heading', 'id' => 'heading', 'level' => 'h1']], $firstContents);
        $this->assertSame([['text' => 'Heading', 'id' => 'heading', 'level' => 'h1']], $secondContents);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownToc);
    }
}
