# FastVolt Markdown 

Simple and Concise Markdown to HTML Converter for PHP.

# Usage

```php

<?php

use FastVolt\Helper\Markdown;

$sample = " # Hello, World ";

# init Markdown object
$html = Markdown::new();

# set markdown data to convert
$html -> setContent( $sample );

# convert data to markdown
$html -> toHtml(); // <h1>Hello, World</h1>

```
