<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FastVolt\Helper\Markdown;

class MarkupTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Heading Test 1: <h1>
     * 
     * @return void
     */
    public function testHeading1(): void
    {
        $markdown = Markdown::new()
            ->setContent('# hello world')
            ->toHtml(); // <h1>hello 1</h1>

        $this->assertSame('<h1>hello world</h1>', $markdown);
    }

    /**
     * Heading Test 2: <h2>
     * 
     * @return void
     */
    public function testHeading2(): void
    {
        $markdown = Markdown::new()
            ->setContent('## hello world')
            ->toHtml(); // <h2>hello 1</h2>

        $this->assertSame('<h2>hello world</h2>', $markdown);
    }

    /**
     * Heading Test 3: <h3>
     * 
     * @return void
     */
    public function testHeading3(): void
    {
        $markdown = Markdown::new()
            ->setContent('### hello world')
            ->toHtml(); // <h3>hello 1</h3>

        $this->assertSame('<h3>hello world</h3>', $markdown);
    }

    /**
     * Heading Test 4: <h4>
     * 
     * @return void
     */
    public function testHeading4(): void
    {
        $markdown = Markdown::new()
            ->setContent('#### hello world')
            ->toHtml(); // <h4>hello 1</h4>

        $this->assertSame('<h4>hello world</h4>', $markdown);
    }

    /**
     * Test Italic 1: <i>
     * 
     * @return void
     */
    public function testItalic(): void
    {
        $markdown = Markdown::new()
            ->setInlineContent('*hello world*')
            ->toHtml(); // <i>hello 1</i>

        $this->assertSame('<i>hello world</i>', $markdown);
    }

    /**
     * Test Link 1: <a>
     * 
     * @return void
     */
    public function testHyperLink(): void
    {
        $markdown = Markdown::new()
            ->setInlineContent('[A LINK](https://github.com/fastvolt)')
            ->toHtml(); // <i>hello 1</i>

        $this->assertSame('<a href="https://github.com/fastvolt">A LINK</a>', $markdown);
    }


    /**
     * Test Inline Markdown Compilation
     * 
     * @return void
     */
    public function testInlineMarkdownCompilation(): void
    {
        $markdown = Markdown::new()
            ->setInlineContent('**hello world with _emphasis_**')
            ->toHtml();

        $this->assertSame('<strong>hello world with <i>emphasis</i></strong>', $markdown);
    }

    /**
     * Test Multi-lined Markdown Compilation
     * 
     * @return void
     */
    public function testMultiLinedMarkdownCompilation(): void
    {
        $markdown = Markdown::new(false)
            ->setInlineContent('>  first world')
            ->setInlineContent('__second word__')
            ->toHtml();

        $this->assertNotNull($markdown);
        $this->assertIsString($markdown);
    }

    /**
     * Test Markdown Compilation With Sanitization Off
     * 
     * @return void
     */
    public function testMarkdownSanitization(): void
    {
        $markdown = Markdown::new(sanitize: true)
            ->setInlineContent('<p>first word</p>')
            ->toHtml();

        $this->assertSame('&lt;p&gt;first word&lt;/p&gt;', $markdown);
    }
}