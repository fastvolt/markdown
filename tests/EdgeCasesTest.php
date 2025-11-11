<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FastVolt\Helper\Markdown;
use Fastvolt\Helper\Markdown\Exceptions\MarkdownFileNotFound;
use Fastvolt\Helper\Markdown\Enums\MarkdownEnum;

class EdgeCasesTest extends \PHPUnit\Framework\TestCase
{
    public function testThrowsExceptionOnMissingInputFile(): void
    {
        $this->expectException(MarkdownFileNotFound::class);
        $this->expectExceptionMessage('non_existent.md');

        Markdown::new()
            ->addFile('non_existent.md')
            ->getHtml(); // This triggers readFile()
    }

    public function testThrowsExceptionOnInvalidFileName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('File Name Must Be A Valid String!');

        Markdown::new()
            ->setContent('a')
            ->addOutputDirectory(__DIR__ . '/pages/')
            ->saveToHtmlFile(' '); // File name is just whitespace
    }

    public function testThrowsExceptionOnLogicErrorWithNoContent(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Set a Markdown Content or File Before Conversion!');

        Markdown::new()->getHtml();
    }
}