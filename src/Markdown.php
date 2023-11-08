<?php

declare(strict_types=1);

namespace FastVolt\Helper;

use FastVolt\Helper\Markdown\Process\ParseMarkdown;

class Markdown
{
    private string $setContent;

    private string $setFile;

    private string $compileDir;


    private function __construct(

        protected bool $sanitize = true

    ) {}


    public static function new(bool $sanitize = true): self
    {
        return new self($sanitize);
    }



    /**
     * Set markdown content to convert to html format
     * 
     * @param string $markdown_content
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
     * @param string $file_name
     * 
     * @return self
     */
    public function setFile(string $file_name)
    {
        $this->setFile = $file_name;
        return $this;
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
     * Set dir where compiled md files will be stored as html
     * 
     * @param string $dir
     */
    public function setCompileDir(string $dir = __DIR__ . '/')
    {
        $this->compileDir = !str_ends_with($dir, '/') ? $dir . '/' : $dir;
        return $this;
    }


    /**
     * Convert Md File to Html File
     * 
     * @return bool
     */
    public function toHtmlFile(string $file_name = 'compiledmarkdown.html'): bool
    {
        if (isset($this->setFile) && isset($this->compileDir) && isset($file_name)) {

            # replace '.md' to '.html' as file extension
            $check_filename = preg_match('/(^\s+)/', $file_name);

            # check if file name is valid and acceptable
            if ($check_filename) {
                return throw new \InvalidArgumentException( 'File Name Must Be A Valid String!' );
            }

            # convert md file content to html
            $file_to_md = $this->convertToHtml($this->read_file($this->setFile));

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


    /**
     * Read and File File Contents
     * 
     * @param string $filename
     * 
     * @return ?string
     */
    private function read_file(string $filename): ?string
    {
        if (!file_exists($filename)) {
            if(! $opn = fopen($filename, 'r+')) {
                return throw new \Exception('File Does Not Exist');
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
    private function convertToHtml( string $markdown ): ?string
    {
        $result = new ParseMarkdown();

        return $result->markdown_text($markdown);   
    }

}