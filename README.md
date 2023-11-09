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
print $html -> toHtml(); // <h2>Hello, World</h2>

```

## Convert Markdown File to Html

Assuming we created a `sample.md` file in `/assets` folder with the following markdown contents:

> **file:** assets/sample.md

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

$file_directory = __DIR__ . '/assets/sample.md';

$html = Markdown::new();

 # set markdown file to compile
$html -> setFile( $file );

 # convert to html
print $html -> toHtml();

// output: <h1>Topic</h1> <h2> Sub-topic</h2> <b>Author:</b> <i>vincent</i>

```
<br>

## Convert Markdown File to Html File

In order to achieve this, you need to create a folder to store the compiled markdown files.

> ➡️ If we've already set up directories named `pages` and `markdowns` with a file named `hello.md` in the `markdowns` directory, let's see how we can convert the `hello.md` markdown file into an HTML file. Afterward, we will save the resulting HTML output in a new file named `hello.html` in `pages` directory:
<br>

> **file:** markdowns/hello.md

```md
### hello
```
<br>

> **file:** index.php

```php

use FastVolt\Helper\Markdown;

# convert md file to html file
$markdown = Markdown::new() 
  # set markdown file to compile
  -> setFile( __DIR__ . '/files/hello.md' )

  # set directory to store compiled html files 
  -> setCompileDir( __DIR__ . '/pages/' )

  # convert to html
  -> toHtmlFile( filename: 'hello' ); 

# check if markdown compile to html successfully 
if ($markdown) {
   print ("compile successful");
}

```

After above operation, you will get the following result:

> **file:** pages/hello.html

```html

<h3>hello</h3>

```
<br>


## Requirements 
- PHP 8.1
- that's all 😇.


## Note
FastVolt's Markup Library is an extended/simplified version of <a href="https://github.com/erusev/parsedown">Erusev's ParseDown Library</a>.

<hr>


Released under [MIT](/LICENSE) by [@fastvolt](https://github.com/fastvolt).
