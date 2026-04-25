<?php

use PHPUnit\Framework\TestCase;

class HeadingElementTest extends TestCase
{
    protected $parsedownToc;
    
    protected function setUp(): void
    {
        $this->parsedownToc = new ParsedownToc();
        $this->parsedownToc->setSafeMode(true);
    }

    /**
     * Test case for the blockHeader method.
     */
    public function testBlockHeader()
    {
        $line = [
            'body' => "# 1.1 Headings",
            'indent' => 0,
            'text' => "# 1.1 Headings"
        ];

        $actualBlock = $this->invokeMethod($this->parsedownToc, 'blockHeader', [$line]);
        $this->assertSame('h1', $actualBlock['element']['name']);
        $this->assertSame('1-1-headings', $actualBlock['element']['attributes']['id']);
        $this->assertSame('1.1 Headings', $this->extractHeadingText($actualBlock));
    }

    /**
     * Test case for the blockSetextHeader method.
     *
     * This method tests the behavior of the blockSetextHeader method
     * It verifies that the method correctly converts a setext header block into an h1 element.
     */
    public function testBlockSetextHeader()
    {
        $line = [
            'body' => "==========",
            'indent' => 0,
            'text' => "=========="
        ];

        $block = $this->createSetextHeaderBlock('Alt-H1');

        $actualBlock = $this->invokeMethod($this->parsedownToc, 'blockSetextHeader', [$line, $block]);
        $this->assertSame('h1', $actualBlock['element']['name']);
        $this->assertSame('alt-h1', $actualBlock['element']['attributes']['id']);
        $this->assertSame('Alt-H1', $this->extractHeadingText($actualBlock));
        $this->assertTrue($actualBlock['identified']);
    }

    public function testCustomHeadingIdIsReservedFromAutoGeneration()
    {
        $markdown = "testing\n\n# Heading {#heading-1}\n\n## Heading\n\n### Heading\n\n## Heading";

        $html = $this->parsedownToc->body($markdown);

        $this->assertStringContainsString('<h1 id="heading-1">Heading</h1>', $html);
        $this->assertStringContainsString('<h2 id="heading">Heading</h2>', $html);
        $this->assertStringContainsString('<h3 id="heading-2">Heading</h3>', $html);
        $this->assertStringContainsString('<h2 id="heading-3">Heading</h2>', $html);
    }

    private function extractHeadingText(array $block): string
    {
        $element = $block['element'];

        if (isset($element['text'])) {
            return $element['text'];
        }

        if (isset($element['handler']['argument'])) {
            return $element['handler']['argument'];
        }

        return '';
    }

    private function createSetextHeaderBlock(string $text): array
    {
        return [
            'type' => 'Paragraph',
            'element' => [
                'name' => 'p',
                'text' => $text,
                'handler' => [
                    'function' => 'lineElements',
                    'argument' => $text,
                    'destination' => 'elements',
                ],
            ],
            'identified' => true,
        ];
    }

    /**
     * Invokes a protected or private method of an object using reflection.
     *
     * @param object $object The object whose method needs to be invoked.
     * @param string $methodName The name of the method to be invoked.
     * @param array $parameters An array of parameters to be passed to the method.
     * @return mixed The result of the method invocation.
     */
    protected function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }

    protected function tearDown(): void
    {
        unset($this->parsedownToc);
    }
}