<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use FastVolt\Helper\Markdown;
use Fastvolt\Helper\Markdown\Enums\MarkdownEnum;

class DirectoryBasedTest extends \PHPUnit\Framework\TestCase
{
    public function testDirectoryToHtmlConversion(): void
    {
        $outputDirB = __DIR__ . '/pages/backup/';

        $markdown = Markdown::new();
        $markdown->setSourceDirectory(directory_name: __DIR__ . '/markdown/');
        $markdown->addOutputDirectory(directory: __DIR__ . '/pages/'); // first html storage dir
        $markdown->addOutputDirectory(directory: $outputDirB); // second html storage dir

        $success = $markdown->run(as: MarkdownEnum::TO_HTML_DIRECTORY);
        $this->assertTrue($success);

        // 1. Check root file conversion and content in Output A
        $filePathA = __DIR__ . '/pages/file1.html';
        $this->assertFileExists($filePathA);

        // 3. Ensure the nested directory was created correctly in Output A
        $this->assertDirectoryExists(__DIR__ . '/pages/backup/');
    }

    public function testDirectoryConversionThrowsExceptionIfSourceDirNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Source directory not set');

        Markdown::new()
            ->addOutputDirectory(__DIR__ . '/pages/')
            ->run(MarkdownEnum::TO_HTML_DIRECTORY);
    }

    public function testDirectoryConversionThrowsExceptionIfOutputDirNotSet(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Output directory not set');

        Markdown::new()
            ->setSourceDirectory(directory_name: __DIR__ . '/markdown/')
            ->run(MarkdownEnum::TO_HTML_DIRECTORY);
    }
}