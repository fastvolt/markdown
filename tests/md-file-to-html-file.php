<?php

require (__DIR__.'/../vendor/autoload.php');

use FastVolt\Helper\Markdown;

# convert md file to html file
$markdown = Markdown::new() 
  -> setFile( __DIR__ . '/files/hello.md' )
  -> setCompileDir( 'pages/' )
  -> toHtmlFile(); 

# check if markdown compile to html successfully 
if ($markdown) {
   print ("compile successful");
} 
