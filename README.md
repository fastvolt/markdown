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
  <a href="https://github.com/fastvolt/markdown/actions/workflows/php.yml">
    <img src="https://github.com/fastvolt/markdown/actions/workflows/php.yml/badge.svg?branch=master" alt="PHP Composer" />
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

---

🚀 Installation

```cmd
composer require fastvolt/markdown
```

---

📦 Basic Usage

```php
use FastVolt\Helper\Markdown;

$text = "## Hello, World";

$markdown = Markdown::new()
    ->setContent($text);

echo $markdown->toHtml();
```

Result:

```html
<h2>Hello, World</h2>
```

---

📄 Convert Markdown File to HTML

> ***sample.md***

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
$markdown = Markdown::new()
    ->setFile('./markdowns/sample.md');

echo $markdown->toHtml();
```

Result
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

---

📝 Compile Markdown to HTML File

blogPost.md

## Here is a Markdown File Waiting To Be Compiled To an HTML File

index.php

```php
$markdown = Markdown::new()
    ->setFile(__DIR__ . '/markdowns/blogPost.md')
    ->setCompileDir(__DIR__ . '/pages/')
    ->toHtmlFile(filename: 'newHTMLFile');

echo "Compiled to ./pages/newHTMLFile.html";
```

---

🔒 Sanitizing HTML Output

You can sanitize input HTML using the sanitize flag:

```php
$markdown = Markdown::new(sanitize: true)
    ->setContent('<h1>Hello World</h1>');

echo $markdown->toHtml();

Output

<p>&lt;h1&gt;Hello World&lt;/h1&gt;</p>
```

---

⚙️ Advanced Use Case

Combine multiple markdown files and inline content:

```php
$markdown = Markdown::new(sanitize: true)
    ->setFile('./markdowns/Header.md')
    ->setInlineContent('_My name is **vincent**, the co-author of this blog_')
    ->setContent('Kindly follow me on my GitHub page via: [@vincent](https://github.com/oladoyinbov).')
    ->setContent('Here are the lists of my projects:')
    ->setContent('
- Dragon CMS
- Fastvolt Framework.
  + Fastvolt Router
  + Markdown Parser.
    ')
    ->setFile('./markdowns/Footer.md')
    ->setCompileDir('./pages/')
    ->setCompileDir('./backup/pages/')
    ->toHtmlFile(file_name: 'homepage');

echo $markdown->toHtml();
```

***Output:***

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

---

✅ Requirements

PHP 8.1 or higher


---

ℹ️ Notes

> This library is an extended and simplified version of the excellent Parsedown by Erusev.


---

📄 License

This project is open-source and licensed under the MIT License by @fastvolt.


---