<?php
use SebastianBergmann\Type\VoidType;

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

    $this->assertSame('<h3>hello</h3>', $markdown);
  }


   /**
   * Test Markdown to Html Conversion
   * 
   * @return void
   */
  public function testMdtoHtml(): void
  {
    $markdown = Markdown::new()
      ->setContent(' ### hello ')
      ->toHtml();

      $this->assertSame('<h3>hello</h3>', $markdown);
  }


   /**
   * Test Markdown File to Html File Conversion
   * 
   * @return void
   */
  public function testMdFiletoHtmlFile(): void
  {
    $markdown = Markdown::new()
      ->setFile( __DIR__ . '/files/hello.md' )
      ->setCompileDir( __DIR__ . '/pages/' )
      ->toHtmlFile( 'mdfiletohtmlfile' );

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
      ->setContent( '# hello' )
      ->setCompileDir( '/pages' )
      ->toHtmlFile( 'test1' );

      $this->assertIsBool($markdown);

      $this->assertTrue($markdown);
  }

}
