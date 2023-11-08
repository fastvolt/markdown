<?php

require (__DIR__.'/../vendor/autoload.php');

use FastVolt\Helper\Markdown;

# convert md to html
$markdown = Markdown::new() 
  -> setFile( __DIR__. '/files/hello.md' ) 
  -> toHtml();

# print result
print ($markdown); // <h3>hello</h3>
