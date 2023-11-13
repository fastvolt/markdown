<?php

require "./vendor/autoload.php";

use FastVolt\Helper\Markdown;

print Markdown::new( true ) -> setContent( '[Parsedown Extra](https://github.com/erusev/parsedown-extra)' ) -> toHtml();