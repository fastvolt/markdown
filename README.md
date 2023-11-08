# Fastvolt Markdown - The Library

A Fast, Simple and Straight-forward Markdown to HTML Converter for PHP.

[![PHP Composer](https://github.com/fastvolt/markdown/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/fastvolt/markdown/actions/workflows/php.yml) [![License](https://img.shields.io/badge/License-MIT-yellow)](#license) [![issues - markdown](https://img.shields.io/github/issues/fastvolt/markdown)](https://github.com/fastvolt/markdown/issues) [![fastvolt - markdown](https://img.shields.io/static/v1?label=fastvolt&message=markdown&color=yellow&logo=github)](https://github.com/fastvolt/markdown "Go to GitHub repo") ![maintained - yes](https://img.shields.io/badge/maintained-yes-blue)


## Usage

```php

<?php

use FastVolt\Helper\Markdown;

$sample = " ## Hello, World ";

# init Markdown object
$html = Markdown::new();

# set markdown data to convert
$html -> setContent( $sample );

# convert data to markdown
$html -> toHtml(); // <h2>Hello, World</h2>

```

## Convert Markdown File

Assuming we created a `sample.md` file in `/resources` folder with the following markdown contents:

> **file:** resources/sample.md

```md 

# Topic
## Sub-topic
**Author:** __vincent__
```
<br>

> **file:** index.php

```php

<?php

use FastVolt\Helper\Markdown;

$file = ' resources/sample.md ';

$html = Markdown::new();

$html -> setFile( $file );

echo $html -> toHtml();

// output: <h1>Topic</h1> <h2> Sub-topic</h2> <b>Author:</b> <i>vincent</i>

```


## Requirements 
- PHP 8.1
- that's all 😇.


# Note
FastVolt is an extended/simplified version of <a href="https://github.com/erusev/parsedown">Erusev's ParseDown Library</a>.

<hr>


Released under [MIT](/LICENSE) by [@fastvolt](https://github.com/fastvolt).