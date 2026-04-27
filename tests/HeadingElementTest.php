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
     * Supports both Parsedown 1.7.x (element.text + handler string)
     * and Parsedown 1.8.x (element.handler array).
     */
    public function testBlockHeader()
    {
        $line = [
            'body' => "# 1.1 Headings",
            'indent' => 0,
            'text' => "# 1.1 Headings",
        ];

        $actualBlock = $this->invokeMethod($this->parsedownToc, 'blockHeader', [$line]);

        $this->assertIsArray($actualBlock);
        $this->assertSame('h1', $actualBlock['element']['name']);
        $this->assertSame('1-1-headings', $actualBlock['element']['attributes']['id']);

        // Retrieve the heading text regardless of Parsedown version
        $text = $actualBlock['element']['text']
            ?? $actualBlock['element']['handler']['argument']
            ?? null;
        $this->assertSame('1.1 Headings', $text);
    }

    /**
     * Test case for the blockSetextHeader method.
     * Supports both Parsedown 1.7.x (element.text + handler string)
     * and Parsedown 1.8.x (element.handler array + type key).
     */
    public function testBlockSetextHeader()
    {
        $line = [
            'body' => "==========",
            'indent' => 0,
            'text' => "==========",
        ];

        if (version_compare(Parsedown::version, '1.8', '>=')) {
            // Parsedown 1.8: paragraph block carries a handler array and requires type = 'Paragraph'
            $block = [
                'type' => 'Paragraph',
                'element' => [
                    'name' => 'p',
                    'handler' => [
                        'function' => 'lineElements',
                        'argument' => 'Alt-H1',
                        'destination' => 'elements',
                    ],
                ],
                'identified' => true,
            ];
        } else {
            // Parsedown 1.7.x: paragraph block uses plain text key
            $block = [
                'element' => [
                    'name' => 'p',
                    'text' => 'Alt-H1',
                    'handler' => 'line',
                ],
                'identified' => true,
            ];
        }

        $actualBlock = $this->invokeMethod($this->parsedownToc, 'blockSetextHeader', [$line, $block]);

        $this->assertIsArray($actualBlock);
        $this->assertSame('h1', $actualBlock['element']['name']);
        $this->assertSame('alt-h1', $actualBlock['element']['attributes']['id']);

        // Retrieve the heading text regardless of Parsedown version
        $text = $actualBlock['element']['text']
            ?? $actualBlock['element']['handler']['argument']
            ?? null;
        $this->assertSame('Alt-H1', $text);
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
