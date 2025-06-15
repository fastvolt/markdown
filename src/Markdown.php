<?php

declare(strict_types=1);

namespace FastVolt\Helper;

use FastVolt\Helper\Libs\Markdown\ParseMarkdown;

final class Markdown
{
    private array $contents;
    private array $compileDir = [];

    public function __construct(
        # sanitize outputs
        protected bool $sanitize = true
    ) {}

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
     * Set Markdown File
     *
     * @param string $file_name: Set File to read markdown content from e.g './markdowns/index.md'
     *
     * @return self
     */
    public function setFile(string $file_name): static
    {
        $this->contents[]['file'] = $file_name;
        return $this;
    }

    /**
     * Set directory where compiled markdown files will be stored in html format
     *
     * @param string $directory directory where your compiled html files will be stored
     */
    public function setCompileDir(string $directory = './markdowns/'): static
    {
        try { 
            $compilationDir = !str_ends_with($directory, '/') 
                ? "$directory/" 
                : $directory;
    
            $this->compileDir[] = $compilationDir; 
    
            if (!is_dir($compilationDir)) {
                if (mkdir($compilationDir, 0777)) {
                    return $this;
                }
            }
            return $this;
        } catch (\Exception|\TypeError|\Throwable $e) {
            throw $e;
        }
    }

    /**
     * Read File Contents
     *
     * @param string $filename Input file name
     *
     * @return string|\Exception|null
     */
    private function read_file(string $filename): string|\Exception|null
    {
        if (!file_exists($filename)) {
            $filename = !str_starts_with($filename, '/')
                ? "/{$filename}"
                : $filename;

            if (!file_exists($filename)) {
                throw new \Exception("File Name or Directory ($filename) Does Not Exist!");
            }
        }

        return file_get_contents($filename);
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
     */
    private function validateFileName(string $name): \InvalidArgumentException|bool
    {
        $validateType = preg_match('/(^\s+)/', $name);

        # check if file name is valid and acceptable
        if ($validateType) {
            throw new \InvalidArgumentException('File Name Must Be A Valid String!');
        }

        return true;
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
     * Compile Markdown to Raw HTML Output
     *
     * @return string|null|\LogicException
     */
    public function toHtml(): \LogicException|string|null
    {
        if (!isset($this->contents) || count($this->contents) == 0) {
            throw new \LogicException(
                message: 'Set a Markdown Content or File Before Conversion!'
            );
        }

        // store all compiled html contents here
        $html_contents = [];

        foreach ($this->contents as $key => $single_content) {
            $html_contents[] = match (array_key_first($single_content)) {
                'inline' => $this->compileSingleLinedMarkdown($single_content['inline']),
                'file' => $this->compileMultiLinedMarkdown($this->read_file($single_content['file'])),
                default => $this->compileMultiLinedMarkdown($single_content['multi-line'])
            };
        };

        return implode("\n\r", $html_contents);
    }


    /**
     * Compile Markdown Contents to Html File
     *
     * @param string $file_name: rename compiled html file
     *
     * @return bool|\LogicException
     */
    public function toHtmlFile(string $file_name = 'compiledmarkdown.html'): \LogicException|bool
    {
        // validate file name
        $this->validateFileName($file_name);

        // check if compilation directories are set
        if (!isset($this->compileDir) || count($this->compileDir) == 0) {
            throw new \LogicException('Ensure To Set A Storage Directory For Your Compiled HTML File!');
        }

        $html_contents = [];

        if (isset($this->contents) && count($this->contents) > 0) {
            foreach ($this->contents as $key => $single_content) {
                $html_contents[] = match (array_key_first($single_content)) {
                    'inline' => $this->compileSingleLinedMarkdown($single_content['inline']),
                    'file' => $this->compileMultiLinedMarkdown($this->read_file($single_content['file'])),
                    default => $this->compileMultiLinedMarkdown($single_content['multi-line'])
                };
            };

            # add extension to filename
            $file_name = $this->addHtmlExtension($file_name);

            // Compile The Markdown Contents to Single pr Multiple Directories
            return $this->saveCompiledMarkdownFiles(
                compileDirs: $this->compileDir,
                file_name: $file_name,
                contents: $html_contents
            );
        }
        
        throw new \LogicException('Set A Markdown File or Content to Compile!');
    }

    private function saveCompiledMarkdownFiles(array $compileDirs, string $file_name, array $contents): bool
    {
        if (count($compileDirs) > 0) {
            foreach ($compileDirs as $single_directory) {
                if (!is_dir($single_directory)) {
                    throw new \RuntimeException("Failed To Locate ('{$single_directory}') Directory!");
                }

                # write md to html file
                if ($create_file = fopen("{$single_directory}{$file_name}", 'w+')) {
                   fwrite($create_file, implode("\n\r", $contents));
                   fclose($create_file);
                   continue;
                }
            }
            return true;
        }
        return false;
    }
}
