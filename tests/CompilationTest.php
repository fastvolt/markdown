<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FastVolt\Helper\Markdown;
use Fastvolt\Helper\Markdown\Enums\MarkdownEnum;

class CompilationTest extends \PHPUnit\Framework\TestCase
{
  /**
   * Test Markdown to Html Conversion
   * 
   * @return void
   */
  public function testMdtoHtml(): void
  {
    $markdown = Markdown::new()
      ->setInlineContent('_This is an inline markdown content_')
      ->toHtml(); // <h1>hello 1</h1>

    $this->assertSame(expected: '<i>This is an inline markdown content</i>', actual: $markdown);
  }

  /**
   * Test Markdown Compilation
   * 
   * @return void
   */
  public function testMarkdownCompilation(): void
  {
    $markdown = Markdown::new()
      ->setContent('# Title')
      ->setContent('# Sub-Title')
      ->setInlineContent('_first word with_')
      ->setInlineContent('[A LINK](https://github.com/fastvolt)')
      ->toHtml();

    $this->assertIsString($markdown);
  }

  public function testGetHtmlFromMixedContent(): void
  {
    $markdown = Markdown::new(true); // Test with sanitize=true (default)

    // Multi-line content
    $markdown->setContent("## Heading 2");

    $markdown->setContent("## Title\n* List Item");
                                     
    // Inline content
    $markdown->setInlineContent('This is **bold**');

    // File content
    $markdown->addFile(__DIR__ . '/markdown/file1.md');

    // File content
    $markdown->addFile(__DIR__ . '/markdown/test.md');

    $html = $markdown->getHtml();

    // Check for core elements from all sources
    //$this->assertStringContainsString('<h2>Title</h2>', $html);
    $this->assertStringContainsString('<li>List Item</li>', $html);
    //$this->assertStringContainsString('This is <strong>bold</strong>', $html);
    //$this->assertStringContainsString('<h1>Header 1</h1>', $html);

    // Test run() alias
    $html_alias = $markdown->run(MarkdownEnum::TO_HTML);
    $this->assertSame($html, $html_alias);
  }

  public function testSaveToHtmlFileInSingleDirectory(): void
  {
    $markdown = Markdown::new();
    $markdown->setContent('Test Content');
    $markdown->addOutputDirectory(directory: __DIR__ . '/pages');

    $success = $markdown->saveToHtmlFile(file_name: 'test-output'); // Should automatically add .html
    $this->assertTrue($success);

    $filePath = __DIR__ . '/pages/test-output.html';
    $this->assertFileExists($filePath);
    $this->assertStringContainsString('<p>Test Content</p>', file_get_contents($filePath));
  }

  public function testSaveToHtmlFileInMultipleDirectories(): void
  {
    $outputDirB = __DIR__ . '/pages/backup/';

    $markdown = Markdown::new();
    $markdown->setContent('Multi-Save');

    // Add two different output directories
    $markdown->addOutputDirectory(directory: __DIR__ . '/pages/');
    $markdown->addMultipleOutputDirectories(directories: [$outputDirB]); // Test multiple method

    $success = $markdown->saveToHtmlFile(file_name: 'multi.html');
    $this->assertTrue($success);

    // Check existence in both directories
    $this->assertFileExists(filename: __DIR__ . '/pages/multi.html');
    $this->assertFileExists(filename: $outputDirB . 'multi.html');
  }

  public function testFileSavingThrowsExceptionIfNoOutputDirSet(): void
  {
    $this->expectException(\LogicException::class);
    $this->expectExceptionMessage('Ensure To Set A Storage Directory');

    Markdown::new()
      ->setContent('a')
      ->saveToHtmlFile();
  }

  /**
   * Test Markdown Compilation
   * 
   * @return void
   */
  public function testMarkdownAdvancedCompilation(): void
  {
    $markdown = Markdown::new(sanitize: true)
      ->addFile(__DIR__ . '/markdown/header.md')
      ->setInlineContent('_My name is **vincent**, the co-author of this blog_')
      ->setContent('Kindly follow me on my github page via: [@vincent](https://github.com/oladoyinbov).')
      ->setContent('Here are the lists of my projects:')
      ->setContent('
        - Dragon CMS
        - Fastvolt Framework.
            + Fastvolt Router
            + Markdown Parser.
            ')
      ->addFile(__DIR__ . '/markdown/footer.md');


    // set compilation directory
    $markdown->addOutputDirectory(__DIR__ . '/pages/');

    // set second compilation directory (OPTIONAL)
    $markdown->addOutputDirectory(__DIR__ . '/pages/backup/');

    // Compile The Markdown with File Name 'homepage'
    $result = $markdown->saveToHtmlFile(file_name: 'homepage');

    $this->assertIsBool($result);

    $this->assertTrue($result === true);
  }
}