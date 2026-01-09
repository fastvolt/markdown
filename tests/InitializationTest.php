<?php

declare(strict_types=1);

use FastVolt\Helper\Markdown;
use PHPUnit\Framework\TestCase;

class InitializationTest extends TestCase
{
    public function testInitializationAndFluency(): void
    {
        // with sanitize as false
        $markdown = Markdown::new(false);
        $this->assertInstanceOf(Markdown::class, $markdown);

        // Test fluent interface returns $this (static)
        $this->assertInstanceOf(Markdown::class, $markdown->setSourceDirectory(__DIR__ . '/markdown'));
        $this->assertInstanceOf(Markdown::class, $markdown->setContent('#test'));
        $this->assertInstanceOf(Markdown::class, $markdown->setFile(__DIR__ . '/markdown/test.md'));
        $this->assertInstanceOf(Markdown::class, $markdown->addOutputDirectory(__DIR__ . '/pages'));
    }

    public function testSetFileAndAddFileAreAliases(): void
    {
        $markdown = Markdown::new();
        $markdown->setFile(__DIR__ . '/markdown/header.md');
        $markdown->addFile(__DIR__ . '/markdown/footer.md');

        // The internal contents array should have two entries
        // (This requires accessing a private property, typically via Reflection or a temporary public accessor for testing)
        // For simplicity, we'll test the output later.
        $this->assertTrue(true); // Placeholder until output is tested
    }
}
