<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FastVolt\Helper\Markdown;

class CompilationTest extends \PHPUnit\Framework\TestCase
{
  /**
   * Test Markdown File to Html Conversion
   * 
   * @return void
   */
  public function testMdFileToHtml(): void
  {
    # convert md to html
    $markdown = Markdown::new()
      ->setFile(file_name: __DIR__ . '/files/hello.md')
      ->toHtml();

    $this->assertSame(expected: '<h1>hello 1</h1>', actual: $markdown);
  }


  /**
   * Test Markdown to Html Conversion
   * 
   * @return void
   */
  public function testMdtoHtml(): void
  {
    $markdown = Markdown::new()
      ->setInlineContent('> This is an inline markdown content')
      ->setContent(content: ' # hello ')
      ->toHtml(); // <h1>hello 1</h1>

    $this->assertSame(expected: '<h1>hello</h1>', actual: $markdown);
  }


  /**
   * Test Markdown File to Html File Conversion
   * 
   * @return void
   */
  public function testMdFiletoHtmlFile(): void
  {
    $markdown = Markdown::new()
      ->setFile(file_name: './files/hello-2.md')
      ->setCompileDir(directory: './pages/')
      ->toHtmlFile(file_name: 'hello-2.html'); // <h2>hello 2</h2>

    $this->assertIsBool(actual: $markdown);

    $this->assertTrue(condition: $markdown);
  }


  /**
   * Test Markdown Content to Html File Conversion
   * 
   * @return void
   */
  public function testMdContentToHtmlFile(): void
  {
    $markdown = Markdown::new()
      ->setContent(content: '### hello 3')
      ->setCompileDir(directory: './pages/')
      ->toHtmlFile(file_name: 'hello-3.html'); // <h3>hello 3</h3>

    $this->assertIsBool(actual: $markdown);

    $this->assertTrue(condition: $markdown);
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

    $this->assertSame('<h1>Title</h1>

        <h1>Sub-Title</h1>
        
        <i>first word with</i>
        
        <a href="https://github.com/fastvolt">A LINK</a>', $markdown);
  }

  /**
   * Test Markdown Compilation
   * 
   * @return void
   */
  public function testMarkdownAdvancedCompilation(): void
  {
    $markdown = Markdown::new(sanitize: true)
      ->setFile('./files/heading.md')
      ->setInlineContent('_My name is **vincent**, the co-author of this blog_')
      ->setContent('Kindly follow me on my github page via: [@vincent](https://github.com/oladoyinbov).')
      ->setContent('Here are the lists of my projects:')
      ->setContent('
        - Dragon CMS
        - Fastvolt Framework.
            + Fastvolt Router
            + Markdown Parser.
            ')
      ->setFile('./files/footer.md');


    // set compilation directory
    $markdown->setCompileDir('./pages/');

    // set second compilation directory (OPTIONAL)
    $markdown->setCompileDir('./pages/backup/');

    // Compile The Markdown with File Name 'homepage'
    $result = $markdown->toHtmlFile(file_name: 'homepage');

    $this->assertIsBool($result);

    $this->assertTrue($result === true);
  }
}