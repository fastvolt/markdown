# Fastvolt Markdown - The Library

A Simple and Straight-forward Markdown to HTML Converter for PHP.

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

# Convert File Content

Assuming we created a `sample.md` file in `resources` folder with the following markdown contents:

> file: sample.md

```php sample.md

# Topic
## Sub-topic

Hello from the west side
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

// output: <h1>Topic</h1> <h2> Sub-topic</h2> <p>Hello from the west side</p>

```


# Requirements 
- PHP 8.1
- that's all 😇.

