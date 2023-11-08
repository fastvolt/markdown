<?php

declare(strict_types=1);

namespace FastVolt\Helper;

use FastVolt\Helper\Markdown\Process\ParseMarkdown;

class Markdown
{
    private string $setContent;

    private string $setFile;

    private function __construct(

        protected bool $sanitize = true,
        
        protected bool $sanitize_markup = false 

    ){}


    public static function new(bool $sanitize = true): self
    {
        return new self( $sanitize, false );
    }


    public function setContent(string $markdown_content)
    {
        $this->setContent = $markdown_content;
        return $this;
    }


    public function setFile(string $markdown_dir_file)
    {
        $this->setFile = $markdown_dir_file;
        return $this;
    }


    public function toHtml(): ?string
    {
        if (isset($this->setContent)) {

            $file_content = $this->setContent;

        } elseif (isset($this->setFile)) {

            try {

                $read_file = \Amp\File\read($this->setFile);

            } catch (\Exception) {

            }

            $file_content = $read_file;
        }

        # parse content markdown to html
        $mkd = new ParseMarkdown();

        return $mkd->text($file_content);
    }

}