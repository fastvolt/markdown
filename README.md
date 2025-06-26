<p align="center">
  <a href="https://github.com/fastvolt/markdown" target="_blank">
    <img src="https://github.com/fastvolt/branding/blob/1c5280745d9c671313f319b7f07d6706a9f75ea9/media/images/fast-mrk.png" alt="Fastvolt" width="160" height="160" />
  </a>
</p>


<h1 align="center">Markdown Parser for PHP</h1>

<p align="center">
  <strong>A fast, simple, and straightforward Markdown to HTML converter for PHP.</strong>
</p>

<p align="center">
  <a href="https://github.com/fastvolt/markdown/actions/workflows/validator1.yml">
    <img src="https://github.com/fastvolt/markdown/actions/workflows/validator1.yml/badge.svg" alt="PHP Composer" />
  </a>
  <a href="#license">
    <img src="https://img.shields.io/badge/License-MIT-yellow" alt="License: MIT">
  </a>
  <a href="https://github.com/fastvolt/markdown/issues">
    <img src="https://img.shields.io/github/issues/fastvolt/markdown" alt="GitHub Issues" />
  </a>
  <a href="https://github.com/fastvolt/markdown">
    <img src="https://img.shields.io/static/v1?label=fastvolt&message=markdown&color=yellow&logo=github" alt="Repo" />
  </a>
  <img src="https://img.shields.io/badge/maintained-yes-blue" alt="Maintained: Yes" />
</p>


## 🚀 Installation

```sh
composer require fastvolt/markdown
```

<br>

## 📦 Basic Usage

```php
use FastVolt\Helper\Markdown;

$text = "## Hello, World";

// initialize markdown object
$markdown = new Markdown(); // or Markdown::new()

// set markdown content 
$markdown->setContent($text);

// compile as raw HTML
echo $markdown->toHtml();
```

#### Output:

```html
<h2>Hello, World</h2>
```

<br>


## 📄 Convert Markdown File to Raw HTML

> ***sample.md:***

```md
#### Heading 4
### Heading 3
## Heading 2
# Heading 1

- List 1
- List 2

> THIS IS A BLOCKQUOTE

[A LINK](https://github.com/fastvolt)
```


> ***index.php:***

```php
$markdown = Markdown::new();

// set markdown file to parse 
$markdown->setFile('./sample.md');

// compile as raw HTML
echo $markdown->toHtml();
```

> ***Output:***

```html
<h4>Heading 4</h4>
<h3>Heading 3</h3>
<h2>Heading 2</h2>
<h1>Heading 1</h1>
<ul>
  <li>List 1</li>
  <li>List 2</li>
</ul>
<blockquote><p>THIS IS A BLOCKQUOTE</p></blockquote>
<a href="https://github.com/fastvolt">A LINK</a>
```

<br>

## 📝 Compile Markdown to HTML File

> ***blogPost.md:***

```md
Here is a Markdown File Waiting To Be Compiled To an HTML File
```

> ***index.php:***

```php

$markdown = Markdown::new()
    // set markdown file
    ->setFile(__DIR__ . '/blogPost.md')
    // set compilation directory 
    ->setCompileDir(__DIR__ . '/pages/')
    // compile as an html file 'newHTMLFile.html'
    ->toHtmlFile(filename: 'newHTMLFile');

if ($markdown) {
  echo "Compiled to ./pages/newHTMLFile.html";
}

```

<br>

## 🔒 Sanitizing HTML Output (XSS Protection)

You can sanitize input HTML and prevent cross-site scripting (XSS) attack using the `sanitize` flag:

```php
$markdown = Markdown::new(
   sanitize: true
);

$markdown->setContent('<h1>Hello World</h1>');

echo $markdown->toHtml();
```

> ***Output:***

```html
<p>&lt;h1&gt;Hello World&lt;/h1&gt;</p>
```

<br>

## ⚙️ Advanced Use Case

### Inline Markdown
```php
$markdown = Markdown::new();

$markdown->setInlineContent('_My name is **vincent**, the co-author of this blog_');

echo $markdown->toHtml();
```

> ***Output:***

```html
<i>My name is <strong>vincent</strong>, the co-author of this blog</i>
```

> ***NOTE:*** Some markdown symbols are not supported with this method

<br>

### Example #1
Combine multiple markdown files, contents and compile them in multiple directories:

> ***Header.md***
```md
# Blog Title  
### Here is the Blog Sub-title
```

> ***Footer.md***
```md
### Thanks for Visiting My BlogPage
```

> ***index.php***

```php
$markdown = Markdown::new(sanitize: true)
    // include header file's markdown contents
    ->setFile('./Header.md')
    // body contents
    ->setInlineContent('_My name is **vincent**, the co-author of this blog_')
    ->setContent('Kindly follow me on my GitHub page via: [@vincent](https://github.com/oladoyinbov).')
    ->setContent('Here are the lists of my projects:')
    ->setContent('
- Dragon CMS
- Fastvolt Framework.
  + Fastvolt Router
  + Markdown Parser.
    ')
    // include footer file's markdown contents
    ->setFile('./Footer.md')
    // set compilation directory 
    ->setCompileDir('./pages/')
    // set another compilation directory to backup the result
    ->setCompileDir('./backup/pages/')
    // compile and store as 'homepage.html'
    ->toHtmlFile(file_name: 'homepage');

if ($markdown) {
   echo "Compile Successful";
}
```

> ***Output:*** `pages/homepage.html`, `backup/pages/homepage.html`

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
    </ul>
  </li>
</ul>
<h3>Thanks for Visiting My BlogPage</h3>
```

<br>

## Supported Formatting Symbols 

| Markdown Syntax              | Description                 | Example Syntax                           | Rendered Output                        |
|-----------------------------|-----------------------------|-------------------------------------------|----------------------------------------|
| `#` to `######`             | Headings (H1–H6)            | `## Heading 2`                            | <h2>Heading 2</h2>                     |
| `**text**` or `__text__`    | Bold                        | `**bold**`                                | <strong>bold</strong>                  |
| `*text*` or `_text_`        | Italic                      | `*italic*`                                | <em>italic</em>                        |
| `~~text~~`                  | Strikethrough               | `~~strike~~`                              | <del>strike</del>                      |
| `` `code` ``                | Inline code                 | `` `echo` ``                              | <code>echo</code>                      |
| <code>```<br>code block<br>```</code> | Code block              | ```` ```php\n echo "Hi"; \n``` ````       | `<pre><code>...</code></pre>`          |
| `-`, `+`, or `*`            | Unordered list              | `- Item 1`<br>`* Item 2`                  | `<ul><li>Item</li></ul>`              |
| `1.` `2.`                   | Ordered list                | `1. Item`<br>`2. Item`                    | `<ol><li>Item</li></ol>`              |
| `[text](url)`               | Hyperlink                   | `[GitHub](https://github.com)`           | <a href="https://github.com">GitHub</a> |
| `> blockquote`              | Blockquote                  | `> This is a quote`                      | <blockquote>This is a quote</blockquote> |
| `---`, `***`, `___`         | Horizontal Rule             | `---`                                     | `<hr>`                                |
| `![alt](image.jpg)`         | Image                       | `![Logo](logo.png)`                      | `<img src="logo.png" alt="Logo">`     |
| `\`                         | Escape special character    | `\*not italic\*`                          | *not italic* (as text)                |

<br>

## ✅ Requirements

PHP 8.1 or newer.

<br>

## ℹ️ Notes

> This library is an extended and simplified version of the excellent [Parsedown](https://github.com/erusev/parsedown/) by Erusev.

<br>

## 📄 License

This project is open-source and licensed under the MIT License by @fastvolt.
