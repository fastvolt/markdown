<?php

use FastVolt\Helper\Markdown;

$markdown = Markdown::new() ->setContent(' # hello ') -> toHtml();

echo $markdown;