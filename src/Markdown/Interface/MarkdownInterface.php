<?php

declare(strict_types=1);

namespace FastVolt\Helper\Markdown\Interface;

use FastVolt\Helper\Markdown\Enums\MarkdownEnum;

interface MarkdownInterface
{
    /**
     * Creates and initializes a new Markdown parser instance.
     *
     * @param bool $sanitize Whether to sanitize the HTML output. Defaults to true.
     * @return self
     */
    public static function new(bool $sanitize = true): self;

    /**
     * Sets the root directory from which Markdown files will be read for directory-based conversion.
     *
     * @param string $directoryName The path to the source directory.
     * @return static
     */
    public function setSourceDirectory(string $directoryName): static;

    /**
     * Adds multi-line Markdown content for compilation.
     *
     * @param string $content The multi-line Markdown text.
     * @return static
     */
    public function setContent(string $content): static;

    /**
     * Adds single-line Markdown content for compilation (for inline parsing).
     *
     * @param string $content The inline Markdown text.
     * @return static
     */
    public function setInlineContent(string $content): static;

    /**
     * Adds a single Markdown file to the queue for compilation.
     *
     * @param string $fileName The path to the Markdown file (e.g., './markdowns/index.md').
     * @return static
     */
    public function addFile(string $fileName): static;

    /**
     * Adds multiple Markdown files to the compilation queue.
     *
     * @param array $fileNames An array of paths to Markdown files.
     * @return static
     */
    public function addMultipleFiles(array $fileNames): static;

    /**
     * Adds a directory where compiled HTML files will be stored.
     * Multiple output directories can be set. Creates the directory if it doesn't exist.
     *
     * @param string $directory The path to the output directory.
     * @return static
     */
    public function addOutputDirectory(string $directory = './markdowns/'): static;

    /**
     * Adds multiple directories where compiled HTML files will be stored.
     *
     * @param array $directories An array of paths to output directories.
     * @return static
     */
    public function addMultipleOutputDirectories(array $directories = ['./markdowns/']): static;

    /**
     * Compiles all set content (via setContent, addFile, etc.) and returns the raw HTML output as a string.
     *
     * @return string|null The compiled HTML string, or null if content is empty or compilation fails.
     */
    public function getHtml(): ?string;

    /**
     * Compiles all set content and saves the combined output to the specified HTML file in the output directory(s).
     *
     * @param string $fileName The name of the HTML file to create (e.g., 'index.html').
     * @return bool True on success, false otherwise.
     */
    public function saveToHtmlFile(string $fileName = 'index.html'): bool;

    /**
     * Runs the main conversion process based on the specified enumeration type.
     *
     * @param MarkdownEnum $as The conversion type (TO_HTML, TO_HTML_FILE, TO_HTML_DIRECTORY).
     * @param string|null $fileName Used only for `TO_HTML_FILE` option.
     * @return mixed Returns bool (for save/directory operations) or string|null (for raw HTML output).
     */
    public function run(MarkdownEnum $as, ?string $fileName = 'index.html'): mixed;
}