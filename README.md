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

// Initialize the parser
$markdown = new Markdown(); // or Markdown::new()

// set markdown content 
$markdown->setContent($text);

// compile and get as raw HTML
echo $markdown->getHtml();
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

// add markdown file to parse 
$markdown->addFile(__DIR__ . '/sample.md');

// compile and get as raw html
echo $markdown->getHtml();
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

## 📝 Convert Markdown File to An HTML File

> ***blogPost.md:***

```md
Here is a Markdown File Waiting To Be Compiled To an HTML File
```

> ***index.php:***

```php

$markdown = Markdown::new()
    // add markdown file
    ->addFile(__DIR__ . '/blogPost.md')

    // add output directory
    ->addOutputDirectory(__DIR__ . '/pages/')

    // compile as an html file
    ->saveToHtmlFile(filename: 'index.html');

if ($markdown) {
  echo "Compiled to ./pages/index.html";
}

```

<br>

## Convert Directory to HTML Directory Structure

This compiles all `.md` files in a source directory into a mirrored structure of `.html` files in an output directory.

```php
use FastVolt\Helper\Markdown;
use FastVolt\Helper\Markdown\Enums\MarkdownEnum;

$markdown = Markdown::create()
    // Set the source directory to read all .md files from (including sub-directories)
    ->setSourceDirectory(__DIR__ . '/docs/')
    
    // Set the output directory to compile the mirrored HTML structure to (Alias: ->setCompileDir())
    ->addOutputDirectory(__DIR__ . '/public/')
    
    // Run the directory conversion process
    ->run(MarkdownEnum::TO_HTML_DIRECTORY);

if ($markdown) {
    echo "Directory conversion successful!";
}

// If '/docs/guide/*.md' exists, it creates '/public/guide/*.html'.
```

<br>

## Single Point Execution

This is the universal executor that can operate in three different modes using the `MarkdownEnum` enum and `run` method.

### Interface

```php
  run(
    MarkdownEnum $as, 
    ?string $fileName
  ): mixed;
```

### MarkdownEnum Interface

```php
enum MarkdownEnum 
{
  // convert markdown source to raw html (raw/file => raw html)
  case TO_HTML;

  // convert markdown source to an html file (markdown raw/file => html file)
  case TO_HTML_FILE;

  // convert markdown source directory to html directory (markdown directory => html directory)
  case TO_HTML_DIRECTORY;
}
```

### Usage Examples

#### Using The `MarkdownEnum::TO_HTML` Enum
> This is an alternative way to call `getHtml()`.

```php
Markdown::new()
    ->setContent('# Heading 1')
    ->run(MarkdownEnum::TO_HTML);
```

#### Using The `MarkdownEnum::TO_HTML_FILE` Enum
> This is an alternative way to call `saveToHtmlFile()`.

```php
Markdown::new()
    ->addOutputDirectory(__DIR__ . '/build')
    ->run(MarkdownEnum::TO_HTML_FILE, 'index.html');
```

#### Using The `MarkdownEnum::TO_HTML_DIRECTORY` Enum
> This is the only method that uses `setSourceDirectory()`. It crawls the source directory, converts all .md files, and saves them (preserving the folder structure) to the output directory.

```php
Markdown::new()
    ->setSourceDirectory(__DIR__ . '/src/my-docs')
    ->addOutputDirectory(__DIR__ . '/public/docs')
    ->run(MarkdownEnum::TO_HTML_DIRECTORY);
```

<br>

## 🔒 Sanitizing HTML Output (XSS Protection)

You can sanitize input HTML and prevent cross-site scripting (XSS) attack using the `sanitize` flag.

> `$sanitize`: Set to `true` (default) to escape HTML tags in the Markdown. Set to `false` only if you completely trust the source of your Markdown and need raw HTML to be rendered.

```php
$markdown = Markdown::new(
  sanitize: true
);

$markdown_unsafe = Markdown::new(
  sanitize: false
);

$content = '<h1>Hello World</h1>';

echo $markdown
  ->setContent($content)
  ->getHtml();

echo $markdown_unsafe
  ->setContent($content)
  ->getHtml();
```

> ***Output:***

```html
Sanitize Enabled: <p>&lt;h1&gt;Hello World&lt;/h1&gt;</p>

Sanitize Disabled: <h1>Hello World</h1>
```

<br>

## ⚙️ Advanced Use Case

### Inline Markdown
```php
$markdown = Markdown::new();

$markdown->setInlineContent('_My name is **vincent**, the co-author of this blog_');

echo $markdown->getHtml();
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
    ->addFile('./Header.md')
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
    ->addFile(__DIR__ . '/Footer.md')
    
    // add the main compilation directory 
    ->addOutputDirectory(__DIR__ . '/pages/')
    
    // add another compilation directory to backup the result
    ->addOutputDirectory(__DIR__ . '/backup/pages/')

    // compile and store as 'index.html'
    ->saveToHtmlFile(file_name: 'index.html');

if ($markdown) {
  echo "Compile Successful. Files created in /pages/ and /backup/pages/";
}
```

> ***Output:*** `pages/index.html`, `backup/pages/index.html`

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

## Error Handling
The parser uses custom exceptions for clarity:

- `MarkdownFileNotFound`: Thrown when a file specified in `addFile()` or a directory in `setSourceDirectory()` does not exist.
- `LogicException`: Thrown if you try to execute a conversion (`getHtml()` or `saveToHtmlFile()`) before any content (setContent, addFile, etc.) has been added to the queue.
- `RuntimeException`: Thrown if the system fails to create an output directory (mkdir fails) or if a required directory is missing during run() execution.

<br>


## 🧠 Interface Method Reference

The parser uses a fluent (chainable) API. This is your command cheatsheet for configuration and execution:

| Method Name | Return Type | Description |
| :--- | :--- | :--- |
| `::new(bool $sanitize = true)` | `self` | **Initialize** the parser instance. The preferred static factory method. |
| `->setSourceDirectory(string $name)` | `static` | Sets the **input root directory** for whole-directory compilation. |
| `->setContent(string $content)` | `static` | Adds **multi-line** Markdown content (supports lists, headings, etc.) to the queue. |
| `->setInlineContent(string $content)` | `static` | Adds **single-line** Markdown content (*bold*, **italic**) to the queue. |
| `->addFile(string $fileName)` | `static` | Adds a single Markdown file path to the compilation queue. *(Alias: `->setFile()`)* |
| `->addMultipleFiles(array $names)` | `static` | Adds an array of Markdown file paths to the compilation queue. |
| `->addOutputDirectory(string $dir)` | `static` | Adds a directory where the compiled HTML will be saved. Allows multiple targets. *(Alias: `->setCompileDir()`)* |
| `->addMultipleOutputDirectories(array $dirs)` | `static` | Adds an array of directories where the compiled HTML will be saved. |
| `->getHtml()` | `string\|null` | **Execute** compilation and return the raw HTML string. *(Alias: `->toHtml()`)* |
| `->saveToHtmlFile(string $name)` | `bool` | **Execute** compilation and write the output to the specified HTML file(s). *(Alias: `->toHtmlFile()`)* |
| `->run(MarkdownEnum $as, ?string $file)` | `mixed` | Universal command to execute conversion based on the specified `MarkdownEnum` target. |

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
