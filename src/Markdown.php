<?php

declare(strict_types=1);

namespace FastVolt\Helper;

use FastVolt\Helper\Markdown\Enums\MarkdownEnum;
use FastVolt\Helper\Markdown\Libs\ParseMarkdown;
use FastVolt\Helper\Markdown\Interface\MarkdownInterface;
use FastVolt\Helper\Markdown\Exceptions\{
    MarkdownException,
    MarkdownFileNotFound
};

final class Markdown implements MarkdownInterface
{
    private array $contents = [];
    private ?string $readFromDir = null;
    private array $compileDir = [];

    public function __construct(
        # sanitize outputs
        protected bool $sanitize = true
    ) {
    }

    /**
     * Initialize Markdown Parser
     *
     * @param bool $sanitize sanitize html outputs
     *
     * @return self
     */
    public static function new(bool $sanitize = true): self
    {
        return new self($sanitize);
    }

    /**
     * Fetch/Read Markdown Files from a Source Directory and it's Child Directories
     * 
     * @param string $directory_name
     * @return Markdown
     */
    public function setSourceDirectory(string $directory_name): static
    {
        $this->readFromDir = $directory_name;
        return $this;
    }

    /**
     * Set Multi-Lined Markdown Contents
     *
     * @param string $content: markdown contents
     *
     * @return self
     */
    public function setContent(string $content): static
    {
        $this->contents[]['multi-line'] = $content;
        return $this;
    }

    /**
     * Set Inline Markdown Contents
     *
     * @param string $content markdown contents
     *
     * @return self
     */
    public function setInlineContent(string $content): static
    {
        $this->contents[]['inline'] = $content;
        return $this;
    }

    /**
     * Set Markdown File (alias of `addFile` method)
     *
     * @param string $file_name: Set file to read markdown contents from e.g './markdowns/index.md'
     *
     * @return self
     */
    public function setFile(string $file_name): static
    {
        $this->contents[]['file'] = $file_name;
        return $this;
    }

    /**
     * Add/Append Markdown File
     * 
     *  - Alias of `setFile` method
     *
     * @param string $file_name: Set file to read markdown contents from e.g './markdowns/index.md'
     *
     * @return self
     */
    public function addFile(string $file_name): static
    {
        $this->contents[]['file'] = $file_name;
        return $this;
    }

    /**
     * Add/Append Multiple Markdown Files
     *
     * @param array $file_names: Set files to read markdown contents from e.g './markdowns/index.md'
     *
     * @return self
     */
    public function addMultipleFiles(array $file_names): static
    {
        foreach ($file_names as $file) {
            $this->contents[]['file'] = $file;
        }

        return $this;
    }

    /**
     * Set directory where compiled markdown files will be stored in html format
     * 
     *  - Alias of `addOutputDirectory` method
     *
     * @param string $directory directory where your compiled html files will be stored
     */
    public function setCompileDir(string $directory = './markdowns/'): static
    {
        $compilationDir = !str_ends_with($directory, '/')
            ? "$directory/"
            : $directory;

        $this->compileDir[] = $compilationDir;

        if (!is_dir($compilationDir)) {
            if (mkdir($compilationDir, 0755, true) === false) {
                throw new \RuntimeException("Failed to create compilation directory: $compilationDir");
            }
        }
        
        return $this;
    }

    /**
     * Set directory where compiled markdown files will be stored in html format
     * 
     * - Alias of `setCompileDir` method
     *
     * @param string $directory directory where your compiled html files will be stored
     */
    public function addOutputDirectory(string $directory = './markdowns/'): static
    {
        $this->setCompileDir($directory);
        return $this;
    }

    /**
     * Set Multiple Directories Where Compiled Markdown Files Will Be Stored In Html Format
     *
     * @param array $directories directory where your compiled html files will be stored
     */
    public function addMultipleOutputDirectories(array $directories = ['./markdowns/']): static
    {
        foreach ($directories as $directory) {
            $compilationDir = !str_ends_with($directory, '/')
                ? "$directory/"
                : $directory;
    
            $this->compileDir[] = $compilationDir;
    
            if (! is_dir($compilationDir)) {
                if (mkdir($compilationDir, 0755, true) === false) {
                    throw new \RuntimeException("Failed to create compilation directory: $compilationDir");
                }
            }
        }
        
        return $this;
    }

    /**
     * Read File Contents
     *
     * @param string $filename Input file name
     * @throws \Exception
     * @return string|null
     */
    private function readFile(string $filename): string|null
    {
        if (! file_exists($filename)) {
            throw new MarkdownFileNotFound("File name or directory ($filename) does not exist!");
        }

        $content = file_get_contents($filename);

        if ($content === false) {
            throw new MarkdownException("Could not read file ($filename).");
        }

        return $content;
    }

    /**
     * Single Lined Markdown Converter 
     *
     * @return ?string
     */
    private function compileSingleLinedMarkdown(string $markdown): ?string
    {
        $instance = new ParseMarkdown(
            $this->sanitize
        );

        return $instance->line($markdown);
    }

    /**
     * Multi-Lined Markdown Converter
     *
     * @return ?string
     */
    private function compileMultiLinedMarkdown(string $markdown): ?string
    {
        $instance = new ParseMarkdown(
            $this->sanitize
        );

        return $instance->markdown_text($markdown);
    }

    /**
     * Check if File Name is Valid
     * 
     * @throws \InvalidArgumentException
     */
    private function validateFileName(string $name)
    {
        $validateType = preg_match('/(^\s+)/', $name);

        # check if file name is valid and acceptable
        if (empty(trim($name)) || $validateType) {
            throw new \InvalidArgumentException('File Name Must Be A Valid String!');
        }
    }

    /**
     * Add html extension to file name
     * 
     * @param string $file_name replace default output filename
     * 
     * @return ?string
     */
    private function addHtmlExtension(string $file_name): ?string
    {
        return !str_ends_with($file_name, '.html')
            ? "{$file_name}.html"
            : $file_name;
    }

    /**
     * Compiles all content from $this->contents into an array of HTML strings.
     *
     * @throws \LogicException
     */
    private function getCompiledHtmlArray(): array
    {
        if (empty($this->contents)) {
            throw new \LogicException(
                message: 'Set a Markdown Content or File Before Conversion!'
            );
        }

        // we'll store all compiled html contents here
        $html_contents = [];

        foreach ($this->contents as $key => $single_content) {
            $html_contents[] = match (array_key_first($single_content)) {
                'inline' => $this->compileSingleLinedMarkdown($single_content['inline']),
                'file' => $this->compileMultiLinedMarkdown($this->readFile($single_content['file'])),
                default => $this->compileMultiLinedMarkdown($single_content['multi-line'])
            };
        }

        return $html_contents;
    }

    /**
     * Compile Markdown to Raw HTML Output
     * 
     * - Alias: `getHtml()`
     * 
     * @throws \LogicException
     */
    public function toHtml(): ?string
    {
        $html_contents = $this->getCompiledHtmlArray();
        return implode("\n", $html_contents);
    }

    /**
     * Compile Markdown to Raw HTML Output
     * 
     * - Alias: `toHtml()`
     *
     * @throws \LogicException
     */
    public function getHtml(): ?string
    {
        $html_contents = $this->getCompiledHtmlArray();
        return implode("\n", $html_contents);
    }

    /**
     * Compile Markdown Contents to Html File
     * 
     * - Alias: `saveToHtmlFile()`
     *
     * @param string $file_name: name for the generated html file
     * @throws \LogicException
     * @return bool
     */
    public function toHtmlFile(string $file_name = 'index.html'): bool
    {
        $file_name = basename($file_name);

        // validate file name
        $this->validateFileName($file_name);

        // check if compilation directories are set
        if (empty($this->compileDir)) {
            throw new \LogicException('Output directory not set. Use the setCompileDir() or setOutputDirectory() method before conversion process.');
        }

        // throws LogicException if no content is set
        $html_contents = $this->getCompiledHtmlArray();

        // add extension to filename
        $file_name = $this->addHtmlExtension($file_name);

        // Compile The Markdown Contents to Single or Multiple Directories
        return $this->saveCompiledHtml(
            compileDirs: $this->compileDir,
            file_name: $file_name,
            contents: $html_contents
        );
    }

    /**
     * Compile Markdown Contents to an Html File
     * 
     * - Alias: `toHtmlFile()`
     *
     * @param string $file_name: name for the generated html file
     * @throws \LogicException
     * @return bool
     */
    public function saveToHtmlFile(string $file_name = 'index.html'): bool
    {
        return $this->toHtmlFile($file_name);
    }

    /**
     * Runs the main conversion process.
     * 
     * @param MarkdownEnum $as run conversion as MarkdownEnum::TO_HTML, MarkdownEnum::TO_HTML_FILE, MarkdownEnum::TO_HTML_DIRECTORY
     * @param mixed $fileName name for the generated html file (only used for MarkdownEnum::TO_HTML_FILE option)
     * @return bool|string|null
     */
    public function run(MarkdownEnum $as, ?string $fileName = 'index.html'): mixed
    {
        return match ($as) {
            MarkdownEnum::TO_HTML_FILE => $this->toHtmlFile($fileName),
            MarkdownEnum::TO_HTML_DIRECTORY => $this->convertDirectoryToHtml(),
            default => $this->toHtml()
        };
    }

    /**
     * Start Directory to HTML Compilation
     * @throws \RuntimeException
     * @return bool
     */
    private function convertDirectoryToHtml(): bool
    {
        // Simplified and Corrected Check for Source Directory
        if ($this->readFromDir === null) {
            throw new \RuntimeException('Source directory not set. Use setSourceDirectory() before converting a directory.');
        }
    
        // Simplified Check for Compile Directory
        if (empty($this->compileDir)) { 
            throw new \RuntimeException('Output directory not set. Use setCompileDir() or setOutputDirectory() before converting a directory.');
        }

        // Normalize Paths
        $read_from = rtrim($this->readFromDir, '/\\');
        $compile_to = array_map(function (string $item) {
            return rtrim($item, '/\\');
        }, $this->compileDir);

        // remove directory dots
        $directory = new \RecursiveDirectoryIterator(
            directory: $read_from,
            flags: \RecursiveDirectoryIterator::SKIP_DOTS
        );

        // iterate over directories (with priorities)
        $iterator = new \RecursiveIteratorIterator(
            iterator: $directory,
            mode: \RecursiveIteratorIterator::SELF_FIRST
        );

        // Filter to find only files ending in .md
        $regex = new \RegexIterator(
            iterator: $iterator,
            pattern: '/^.*\.md$/i',
            mode: \RegexIterator::GET_MATCH
        );

        foreach ($regex as $file) {
            $this->convertSingleDirectoryFile(
                filePath: $file[0], // $file[0] is the full file path
                readFrom: $read_from,
                compileTo: $compile_to
            );
        }

        return true;
    }

    /**
     * Convert Each Single File in Directory to HTML
     */
    private function convertSingleDirectoryFile(string $filePath, string $readFrom, array $compileTo): void
    {
        // 1. Calculate new paths
        // e.g "subdir/my-post.md"
        $MarkdownFilePath = substr($filePath, strlen($readFrom) + 1);

        // format what the html file will look like
        // e.g "html_output/subdir/my-post.html"
        $HTMLFilePath = preg_replace('/\.md$/i', '.html', $MarkdownFilePath);

        foreach ($compileTo as $singleDirectory) {
            // 2. Create the output directory if it doesn't exist
            $htmlOutputPath = $singleDirectory . '/' . $HTMLFilePath;
            $htmlOutputDir = dirname($htmlOutputPath);

            if (!is_dir($htmlOutputDir)) {
                mkdir($htmlOutputDir, 0755, true);
            }

            // 3. Read, convert, and save
            $markdownContent = file_get_contents($filePath);
            $htmlContents = $this->compileMultiLinedMarkdown($markdownContent);

            file_put_contents($htmlOutputPath, $htmlContents);
        }
    }

    /**
     * Save Compiled Markdown Files to Specified Directory
     * @throws \RuntimeException
     * @return bool
     */
    private function saveCompiledHtml(array $compileDirs, string $file_name, array $contents): bool
    {
        if (empty($compileDirs)) {
            return false;
        }

        $html_string = implode("\n", $contents);
        $all_successful = true;

        foreach ($compileDirs as $single_directory) {
            if (!is_dir($single_directory)) {
                throw new \RuntimeException("Failed To Locate ('{$single_directory}') Directory!");
            }

            $full_path = $single_directory . $file_name;

            if (file_put_contents($full_path, $html_string) === false) {
                $all_successful = false; // Mark failure but continue trying other directories
            }
        }

        return $all_successful;
    }
}