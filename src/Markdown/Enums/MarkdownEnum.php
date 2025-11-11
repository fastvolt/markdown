<?php

namespace Fastvolt\Helper\Markdown\Enums;

use Fastvolt\Helper\Markdown\Exceptions\MarkdownEnumNotFound;

enum MarkdownEnum 
{
    // convert markdown source to raw html (raw/file => raw html)
    case TO_HTML; 

    // convert markdown source to an html file (markdown raw/file => html file)
    case TO_HTML_FILE;

    // convert markdown source directory to html directory (markdown directory => html directory)
    case TO_HTML_DIRECTORY;
}