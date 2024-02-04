<?php

use PHPUnit\Framework\TestCase;

class HeadingElementTest extends TestCase
{
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

        $expected = [
            'element' => [
                'name' => 'h1',
                'text' => '1.1 Headings',
                'attributes' => ['id' => '1-1-headings'],
                'handler' => 'line'
            ]
        ];
        $actualBlock = $this->invokeMethod($this->parsedownToc, 'blockHeader', [$line]);
        $this->assertEquals($expected, $actualBlock);
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

        $block = [
            'element' => [
                'name' => 'p',
                'text' => 'Alt-H1',
                'handler' => 'line'
            ],
            'identified' => true
        ];

        $expected = [
            'element' => [
                'name' => 'h1',
                'text' => 'Alt-H1',
                'attributes' => ['id' => 'alt-h1'],
                'handler' => 'line'
            ],
            'identified' => true
        ];

        $actualBlock = $this->invokeMethod($this->parsedownToc, 'blockSetextHeader', [$line, $block]);
        $this->assertEquals($expected, $actualBlock);
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