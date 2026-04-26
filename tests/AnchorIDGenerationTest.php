<?php

use PHPUnit\Framework\TestCase;

class AnchorIDGenerationTest extends TestCase
{
    protected $parsedownToc;

    protected function setUp(): void
    {
        $this->parsedownToc = new ParsedownToc();
        $this->parsedownToc->setSafeMode(true);
    }

    /**
     * Test case for generating unique anchor IDs without duplicates.
     */
    public function testAnchorID()
    {
        $text = "uniqueheading";
        $this->assertEquals('uniqueheading', $this->invokeMethod($this->parsedownToc, 'createAnchorID', [$text]));
    }

    /**
     * Test case for checking the incrementation of duplicate anchor IDs.
     *
     * @return void
     */
    public function testAnchorIDDuplicate()
    {
        $text = "heading";
        $this->parsedownToc->setReservedIds([]); // Ensure no reserved id interference

        $firstCall = $this->invokeMethod($this->parsedownToc, 'createAnchorID', [$text]);
        $secondCall = $this->invokeMethod($this->parsedownToc, 'createAnchorID', [$text]);

        $this->assertNotEquals($firstCall, $secondCall);
        $this->assertTrue(str_contains($secondCall, $firstCall . '-'));
        $this->assertEquals('heading', $firstCall);
        $this->assertEquals('heading-1', $secondCall);
    }

    /**
     * Test case for custom anchor ID generation callback.
     *
     * This test verifies that the custom anchor ID generation callback is correctly set and applied.
     * It checks if the generated HTML contains the expected anchor ID based on the custom function.
     */
    public function testAnchorIdGenerator()
    {
        $customFunction = function ($text, $options) {
            return mb_strtolower(str_replace(' ', '_', $text));
        };
        $this->parsedownToc->setAnchorIdGenerator($customFunction);

        $markdown = "# custom heading";
        $html = $this->parsedownToc->text($markdown);

        $this->assertStringContainsString('id="custom_heading"', $html);
    }

    /**
    * Test case for generating anchor IDs with reserved ids.
     *
     * This test verifies that the createAnchorID method of the ParsedownToc class
     * generates the correct anchor ID when a blacklist is set and the input text
     * matches an item in the reserved ids list.
     */
    public function testAnchorIDReservedIds()
    {
        $text = "heading";
        $this->parsedownToc->setReservedIds(['heading']);

        $result = $this->invokeMethod($this->parsedownToc, 'createAnchorID', [$text]);
        $this->assertNotEquals('heading', $result);
        $this->assertEquals('heading-1', $result);
    }

    public function testAnchorIDReservedIdsAreTypeStrict()
    {
        $this->parsedownToc->setReservedIds([123]);

        $result = $this->invokeMethod($this->parsedownToc, 'createAnchorID', ['123']);

        $this->assertEquals('123', $result);
    }

    /**
     * Test case for anchor ID heading levels.
     */
    public function testAnchorIDHeadingLevels()
    {
        $this->parsedownToc->setHeadingLevels(['h1']);

        $text = "# heading1";
        $this->parsedownToc->text($text);
        $result = $this->parsedownToc->getContentsList('html');
        $this->assertStringContainsString('<a href="#heading1">heading1</a>', $result);

        $text = "## heading2";
        $this->parsedownToc->text($text);
        $result = $this->parsedownToc->getContentsList('html');
        $this->assertStringNotContainsString('<a href="#heading2">heading2</a>', $result);
    }

    /**
     * Test case for selector-gated IDs and duplicate tracking with ATX headings.
     */
    public function testExcludedATXHeadingDoesNotConsumeGeneratedID()
    {
        $this->parsedownToc->setHeadingLevels(['h2']);

        $excludedHtml = $this->parsedownToc->text("# heading");
        $includedHtml = $this->parsedownToc->text("## heading");

        $this->assertStringContainsString('<h1>heading</h1>', $excludedHtml);
        $this->assertStringNotContainsString('<h1 id="heading">', $excludedHtml);
        $this->assertStringContainsString('<h2 id="heading">heading</h2>', $includedHtml);
    }

    /**
     * Test case for selector-gated IDs and duplicate tracking with Setext headings.
     */
    public function testExcludedSetextHeadingDoesNotConsumeGeneratedID()
    {
        $this->parsedownToc->setHeadingLevels(['h2']);

        $excludedHtml = $this->parsedownToc->text("heading\n===");
        $includedHtml = $this->parsedownToc->text("heading\n---");

        $this->assertStringContainsString('<h1>heading</h1>', $excludedHtml);
        $this->assertStringNotContainsString('<h1 id="heading">', $excludedHtml);
        $this->assertStringContainsString('<h2 id="heading">heading</h2>', $includedHtml);
    }

    /**
     * Test case for sanitizing anchor IDs.
     */
    public function testAnchorIDSanitizeAnchor()
    {
        $text = "heading";
        $result = $this->invokeMethod($this->parsedownToc, 'sanitizeAnchor', [$text]);
        $this->assertEquals('heading', $result);

        $text = "heading with spaces";
        $result = $this->invokeMethod($this->parsedownToc, 'sanitizeAnchor', [$text]);
        $this->assertEquals('heading-with-spaces', $result);

        $text = "heading with special xxxxxxxxxxx@xxxxxxxx";
        $result = $this->invokeMethod($this->parsedownToc, 'sanitizeAnchor', [$text]);
        $this->assertEquals('heading-with-special-xxxxxxxxxxx-xxxxxxxx', $result);
    }

    /**
     * Test case for sanitizing anchor IDs with a custom delimiter.
     */
    public function testAnchorIDSanitizeAnchorCustomDelimiter()
    {
        $this->parsedownToc->setDelimiter('&');

        $text = "heading with spaces";
        $result = $this->invokeMethod($this->parsedownToc, 'sanitizeAnchor', [$text]);
        $this->assertEquals('heading&with&spaces', $result);
    }

    public function testAnchorIDRespectsLowercaseOption()
    {
        $this->parsedownToc->setLowercase(false);

        $html = $this->parsedownToc->text('# Mixed Case');

        $this->assertStringContainsString('<h1 id="Mixed-Case">Mixed Case</h1>', $html);
    }

    public function testAnchorIDRespectsPrefixOption()
    {
        $this->parsedownToc->setPrefix('md-');

        $html = $this->parsedownToc->text('# Heading');

        $this->assertStringContainsString('<h1 id="md-heading">Heading</h1>', $html);
    }

    public function testAnchorIDRespectsReplacements()
    {
        $this->parsedownToc->setReplacements(['cat' => 'dog']);

        $html = $this->parsedownToc->text('# cat nap');

        $this->assertStringContainsString('<h1 id="dog-nap">cat nap</h1>', $html);
    }

    public function testAnchorIDRespectsRegexReplacements()
    {
        $this->parsedownToc->setReplacements(['/cat/' => 'dog']);

        $html = $this->parsedownToc->text('# cat nap');

        $this->assertStringContainsString('<h1 id="dog-nap">cat nap</h1>', $html);
    }

    public function testAnchorIDRespectsTransliteration()
    {
        $this->parsedownToc->setTransliterate(true);

        $html = $this->parsedownToc->text('# Über');

        $this->assertStringContainsString('<h1 id="uber">Über</h1>', $html);
    }

    public function testAnchorIDRespectsUrlencodeOption()
    {
        $this->parsedownToc->setUrlencode(true);

        $html = $this->parsedownToc->text('# Heading Here');

        $this->assertStringContainsString('<h1 id="heading-here">Heading Here</h1>', $html);
    }

    public function testAnchorIDFallsBackWhenSanitizedTextIsEmpty()
    {
        $html = $this->parsedownToc->text("# !!!\n\n# ???");

        $this->assertStringContainsString('<h1 id="section">!!!</h1>', $html);
        $this->assertStringContainsString('<h1 id="section-1">???</h1>', $html);
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
