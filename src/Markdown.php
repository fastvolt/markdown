<?php

declare(strict_types=1);

namespace FastVolt\Helper;

use FastVolt\Helper\Markdown\Process\ParseMarkdown;

class Markdown
{
    # Set Content to parse.
    private string $setContent;

    # Set file to parse.
    private string $setFile;

    # Folder where compiled markdowns should be stored.
    private string $compileDir;


    private function __construct(
        # sanitize outputs
        protected bool $sanitize = true
    ) {

        //
    }


    /**
     * Initialize Markdown Parser
     *
     * @param bool $sanitize Automatically sanitize outputs and do the needful for added security
     *
     * @return self
     */
    public static function new(bool $sanitize = true): self
    {
        return new self($sanitize);
    }


    /**
     * Set markdown content to convert to html format
     *
     * @param string $markdown_content: Input Your Markdown Content Here
     *
     * @return self
     */
    public function setContent(string $markdown_content)
    {
        $this->setContent = $markdown_content;
        return $this;
    }


    /**
     * Set markdown file to read
     *
     * @param string $file_name: Set File to read markdown content from
     *
     * @return self
     */
    public function setFile(string $file_name)
    {
        $this->setFile = $file_name;

        return $this;
    }


    /**
     * Set dir where compiled md files will be stored as html
     *
     * @param string $dir
     */
    public function setCompileDir(string $dir = __DIR__ . '/')
    {
        $this->compileDir = !str_ends_with($dir, '/') ? $dir . '/' : $dir;

        if (!is_dir($this->compileDir)) {
            if (mkdir($this->compileDir, 0777)) {
                return $this;
            }
        }

        return $this;
    }


    /**
     * Read and File File Contents
     *
     * @param string $filename Input file name
     *
     * @return string|\Exception|null
     */
    private function read_file(string $filename): string|\Exception|null
    {
        if (!file_exists($filename)) {

            $filename = !str_starts_with($filename, '/')
                ? '/' . $filename
                : $filename;

            if (!file_exists($filename)) {
                return throw new \Exception("File Name or Directory ($filename) Does Not Exist!");
            }
        }

        return \Amp\File\read($filename);
    }


    /**
     * Convert Md to Html
     *
     * @param string $markdown
     *
     * @return ?string
     */
    private function convertToHtml(string $markdown): ?string
    {
        $result = new ParseMarkdown();

        return $result->markdown_text($markdown);
    }


    /**
     * Check if File Name is Valid
     *
     * @param string $markdown
     *
     * @return bool|int
     */
    private function checkFileName(string $name): bool|int
    {
        return preg_match('/(^\s+)/', $name);
    }



    /**
     * Add html extension to file name
     *
     * @param string $file_name
     *
     * @return ?string
     */
    private function addHtmlExtension(string $file_name): ?string
    {
        return !str_ends_with($file_name, '.html')
            ? $file_name . '.html'
            : $file_name;
    }


    /**
     * Convert md to html
     *
     * @return ?string
     */
    public function toHtml(): ?string
    {
        if (isset($this->setContent)) {

            $read_file = $this->setContent;

        } elseif (isset($this->setFile)) {

            $read_file = $this->read_file($this->setFile);
        }

        return $this->convertToHtml((string) $read_file);
    }


    /**
     * Convert Md File to Html File
     *
     * @param string $file_name: Input your desired file name for the new compiled markdown
     *
     * @return bool
     */
    public function toHtmlFile(string $file_name = 'compiledmarkdown.html'): bool
    {
        # convert if md file is set
        if (isset($this->setFile) && isset($this->compileDir) && isset($file_name)) {

            $check_filename = $this->checkFileName($file_name);

            # check if file name is valid and acceptable
            if ($check_filename) {
                return throw new \InvalidArgumentException('File Name Must Be A Valid String!');
            }

            # convert md file content to html
            $file_to_md = $this->convertToHtml($this->read_file($this->setFile));

            # add extension to filename
            $file_name = $this->addHtmlExtension($file_name);

            # write md to html file
            if ($create_file = fopen($this->compileDir . $file_name, 'w+')) {
                fwrite($create_file, $file_to_md);
                fclose($create_file);
                return true;
            }

            return false;

        # convert if only content is set
        } elseif (isset($this->setContent) && isset($this->compileDir) && isset($file_name)) {

            $check_filename = $this->checkFileName($file_name);

            # check if file name is valid and acceptable
            if ($check_filename) {
                return throw new \InvalidArgumentException('File Name Must Be A Valid String!');
            }

            # convert md file content to html
            $file_to_md = $this->convertToHtml($this->setContent);

            # add extension to filename
            $file_name = $this->addHtmlExtension($file_name);

            # write md to html file
            if ($create_file = fopen($this->compileDir . $file_name, 'w+')) {
                fwrite($create_file, $file_to_md);
                fclose($create_file);
                return true;
            }

            return false;
        }

        return false;
    }
}
