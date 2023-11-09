<?php

declare(strict_types=1);

require(__DIR__ . '/../vendor/autoload.php');

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
      ->setFile(__DIR__ . '/files/hello.md')
      ->toHtml();

    $this->assertSame('<h1>hello 1</h1>', $markdown);
  }


   /**
   * Test Markdown to Html Conversion
   * 
   * @return void
   */
  public function testMdtoHtml(): void
  {
    $markdown = Markdown::new()
      ->setContent(' # hello ')
      ->toHtml(); // <h1>hello 1</h1>

      $this->assertSame('<h1>hello</h1>', $markdown);
  }


   /**
   * Test Markdown File to Html File Conversion
   * 
   * @return void
   */
  public function testMdFiletoHtmlFile(): void
  {
    $markdown = Markdown::new()
      ->setFile( __DIR__ . '/files/hello-2.md' )
      ->setCompileDir( __DIR__ . '/pages/' )
      ->toHtmlFile( file_name: 'hello-2.html' ); // <h2>hello 2</h2>

      $this->assertIsBool($markdown);

      $this->assertTrue($markdown);
  }


     /**
   * Test Markdown Content to Html File Conversion
   * 
   * @return void
   */
  public function testMdContentToHtmlFile(): void
  {
    $markdown = Markdown::new()
      ->setContent( '### hello 3' )
      ->setCompileDir( __DIR__ . '/pages' )
      ->toHtmlFile( file_name: 'hello-3.html' ); // <h3>hello 3</h3>

      $this->assertIsBool($markdown);

      $this->assertTrue($markdown);
  }

}
