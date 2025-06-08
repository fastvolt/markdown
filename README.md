# Fastvolt Markdown - The Library

A Fast, Simple and Straight-forward Markdown to HTML Converter for PHP.

[![PHP Composer](https://github.com/fastvolt/markdown/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/fastvolt/markdown/actions/workflows/php.yml) [![License](https://img.shields.io/badge/License-MIT-yellow)](#license) [![issues - markdown](https://img.shields.io/github/issues/fastvolt/markdown)](https://github.com/fastvolt/markdown/issues) [![fastvolt - markdown](https://img.shields.io/static/v1?label=fastvolt&message=markdown&color=yellow&logo=github)](https://github.com/fastvolt/markdown "Go to GitHub repo") ![maintained - yes](https://img.shields.io/badge/maintained-yes-blue)

## Usage

```php

<?php

use FastVolt\Helper\Markdown;

$text = "## Hello, World";

# initialize markdown instance
$instance = Markdown::new();

# set markdown content
$instance->setContent($text);

# output result as html
echo $html->toHtml(); // <h2>Hello, World</h2>

```

## Convert Markdown File to Html

> **file:** assets/sample.md

```md 

#### Heading 4
### Heading 3
## Heading 2
# Heading 1

- List 1
- List 2

> THIS IS A BLOCKQUOTE

```py 
def Greeting(): 
    return 'hello'
```

[A LINK](https://github.com/fastvolt)

<br>

> **file:** index.php

```php

<?php

use FastVolt\Helper\Markdown;

$markdown_file = './assets/sample.md';

$instance = Markdown::new();

# set the markdown file location
$instance->setFile($markdown_file);

# convert the markdown file to raw HTML output
echo $html->toHtml();

/** OUTPUT: 
 * <h4>Heading 4</h4>
 * <h3>Heading 3</h3>
 * <h2>Heading 2</h2><h1>Heading 1</h1>

 * <ul>
 * <li>List 1</li>
 * <li>List 2</li>
 * </ul>

 * <blockquote><p>THIS IS A BLOCKQUOTE</p></blockquote>

 *  <pre><code class="language-py">def Greeting(): 
 *    return 'hello'</code>
 *  </pre>

 * <a href="https://github.com/fastvolt">A LINK</a>
*/

```
<br>

## Convert Markdown File to Html File

> **file:** markdowns/blogPost.md

```md

## Here is a Markdown Page Waiting To Be Compiled To an HTML File 

```
<br>

> **file:** index.php

```php

use FastVolt\Helper\Markdown;

$markdown = Markdown::new() 
  # set markdown file
  ->setFile(__DIR__ . '/markdowns/blogPost.md')

  # set directory where the compiled html file will be stored
  ->setCompileDir(__DIR__ . '/pages/')

  # execute operation and rename the compiled html file to 'newHTMLFile'
  ->toHtmlFile(filename: 'newHTMLFile'); 

if ($markdown) {
    print("your markdown file 'blogPost.md' has been compiled to './pages/newHTMLFile.html'");
}

```

## Santizing HTML Output
The markdown `new` static method accepts only one parameter which is: 

> `sanitize`: boolean

### Usage Sample

```php

$markdown = Markdown::new(sanitize: true)
    ->setContent('<h1>Hello World</h1>')
    ->toHtml();

```

***Result Output***: `<p>&lt;h1&gt;Hello World&lt;/h1&gt;</p>`.


## Other Advanced Sample
Assuming we have two markdown files with header and footer content for our page.

> **file:** markdowns/Header.md

```md
# Blog Title
### Here is the Blog Sub-title
```

> **file:** markdowns/Footer.md

```md
### Thanks for Visiting My BlogPage
```

Now, let's see how we can include markdown files between our logic:

> **file:** index.php

```php

$markdown = Markdown::new(sanitize: true)
    // add markdown file with heading content
    ->setFile('./markdowns/Header.md')

    // add an inline markdown content
    ->setInlineContent('_My name is **vincent**, the co-author of this blog_')

    // add several markdown contents
    ->setContent('Kindly follow me on my github page via: [@vincent](https://github.com/oladoyinbov).')
    ->setContent('Here are the lists of my projects:')
    ->setContent('
- Dragon CMS
- Fastvolt Framework.
    + Fastvolt Router
    + Markdown Parser.
    ')

    // include markdown file with footer content
    ->setFile('./markdowns/Footer.md');

// set compilation directory
$markdown->setCompileDir('./pages/');

// set another compilation directory to backup our compiled html files
$markdown->setCompileDir('./backup/pages/');
    
// Compile The Markdown with File Name 'homepage'
$saveHTML = $markdown->toHtmlFile(file_name: 'homepage');

if ($saveHTML) {
    // display Compiled HTML 
    echo $markdown->toHTML()
}

```

**Result Output:**
```html

<h1>Blog Title</h1>
<h3>Here is the Blog Sub-title</h3>

<i>My name is <strong>vincent</strong>, the co-author of this blog</i>

<p>Kindly follow me on my github page via: <a href="https://github.com/oladoyinbov">@vincent</a>.</p>

<p>Here are the lists of my projects:</p>

<ul>
<li>Dragon CMS</li>
<li>Fastvolt Framework.
<ul>
<li>Fastvolt Router</li>
<li>Markdown Parser.</li>
</ul></li>
</ul>

<h3>Thanks for Visiting My BlogPage</h3>

```

## Requirements 
- PHP 8.1 or newer.


## Note
FastVolt's Markup Library is an extended/simplified version of [Erusev's ParseDown Library](https://github.com/erusev/parsedown).

<hr>


This library is open-sourced software licensed under the [MIT](/LICENSE) by [@fastvolt](https://github.com/fastvolt).
