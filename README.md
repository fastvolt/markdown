# Fastvolt Markdown - The Library

A Fast, Simple and Straight-forward Markdown to HTML Converter for PHP.

# Usage

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

# Convert Markdown File

Assuming we created a `sample.md` file in `resources` folder with the following markdown contents:

> file: sample.md

```md 

# Topic
## Sub-topic

__Hello from the west side__
```
<br>

> file: index.php

```php

<?php

use FastVolt\Helper\Markdown;

$file = ' resources/sample.md ';

$html = Markdown::init();

$html -> setFile( $file );

echo $html -> toHtml();

// output: <h1>Topic</h1> <h2> Sub-topic</h2> <i>Hello from the west side</i>

```


# Requirements 
- PHP 8.1
- that's all 😇.


# Note
FastVolt is an extended/simplified version of <a href="https://github.com/erusev/parsedown">Erusev's ParseDown Library</a>

