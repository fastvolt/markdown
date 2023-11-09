<?php

declare(strict_types=1);

namespace FastVolt\Helper\Markdown\Process;

class ParseMarkdown
{
    private mixed $breaksEnabled;

    protected bool $safeMode;

    protected mixed $markupEscaped;

    protected const version = '1.7.4';

    private static array $instances = [];

    protected ?array $DefinitionData;

    protected array $specialCharacters = [
        '\\',
        '`',
        '*',
        '_',
        '{',
        '}',
        '[',
        ']',
        '(',
        ')',
        '>',
        '#',
        '+',
        '-',
        '.',
        '!',
        '|',
        ];

    protected array $StrongRegex = [
        '*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*[*])+?)[*]{2}(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*_)+?)__(?!_)/us',
    ];

    protected array $EmRegex = [
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    ];

    protected string $regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*(?:\s*=\s*(?:[^"\'=<>`\s]+|"[^"]*"|\'[^\']*\'))?';

    protected array $voidmarkdownElements = array(
        'area',
        'base',
        'br',
        'col',
        'command',
        'embed',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'source',
    );

    protected array $textLevelmarkdownElements = [
        'a',
        'br',
        'bdo',
        'abbr',
        'blink',
        'nextid',
        'acronym',
        'basefont',
        'b',
        'em',
        'big',
        'cite',
        'small',
        'spacer',
        'listing',
        'i',
        'rp',
        'del',
        'code',
        'strike',
        'marquee',
        'q',
        'rt',
        'ins',
        'font',
        'strong',
        's',
        'tt',
        'kbd',
        'mark',
        'u',
        'xm',
        'sub',
        'nobr',
        'sup',
        'ruby',
        'var',
        'span',
        'wbr',
        'time',
    ];



    protected array $safeLinksWhitelist = [
        'http://',
        'https://',
        'ftp://',
        'ftps://',
        'mailto:',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpeg;base64,',
        'irc:',
        'ircs:',
        'git:',
        'ssh:',
        'news:',
        'steam:',
    ];



    protected array $markdownBlockTypes = [
        '#' => array('Header'),
        '*' => array('Rule', 'List'),
        '+' => array('List'),
        '-' => array('SetextHeader', 'Table', 'Rule', 'List'),
        '0' => array('List'),
        '1' => array('List'),
        '2' => array('List'),
        '3' => array('List'),
        '4' => array('List'),
        '5' => array('List'),
        '6' => array('List'),
        '7' => array('List'),
        '8' => array('List'),
        '9' => array('List'),
        ':' => array('Table'),
        '<' => array('Comment', 'Markup'),
        '=' => array('SetextHeader'),
        '>' => array('Quote'),
        '[' => array('Reference'),
        '_' => array('Rule'),
        '`' => array('FencedCode'),
        '|' => array('Table'),
        '~' => array('FencedCode'),
    ];


    protected array $unmarkedmarkdownBlockTypes = [
        'Code',
    ];


    
    protected array $InlineTypes = [
        '"' => array('SpecialCharacter'),
        '!' => array('Image'),
        '&' => array('SpecialCharacter'),
        '*' => array('Emphasis'),
        ':' => array('Url'),
        '<' => array('UrlTag', 'EmailTag', 'Markup', 'SpecialCharacter'),
        '>' => array('SpecialCharacter'),
        '[' => array('Link'),
        '_' => array('Emphasis'),
        '`' => array('Code'),
        '~' => array('Strikethrough'),
        '\\' => array('EscapeSequence'),
    ];

    # ~

    protected string $inlineMarkerList = '!"*_&[:<>`~\\';



    public function __construct(

        protected bool $sanitize = true,

    ) {
        $this->breaksEnabled = '';
        $this->safeMode = $sanitize;
        $this->setBreaksEnabled = true;

        if(true === $sanitize){
            $this->setUrlsLinked = true;
            $this->setMarkupEscaped = true;
        }
    }


    /**
     * Set Markup Contents
     * 
     * @param string $text
     * 
     * @return bool
     */
    public function markdown_text(string $text): string
    {
        # make sure no definitions are set
        $this->DefinitionData = array();

        # standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        # remove surrounding line breaks
        $text = trim($text, "\n");

        # split text into lines
        $lines = explode("\n", $text);

        # iterate through lines to identify blocks
        $markup = $this->lines($lines);

        # trim line breaks
        $markup = trim($markup, "\n");

        return $markup;
    }


    # Setters
    private function setBreaksEnabled(bool $breaksEnabled): self
    {
        $this->breaksEnabled = $breaksEnabled;

        return $this;
    }


    private function setMarkupEscaped(bool $markupEscaped): self
    {
        $this->markupEscaped = $markupEscaped;

        return $this;
    }


    private function setUrlsLinked(bool $urlsLinked): self
    {
        $this->urlsLinked = $urlsLinked;

        return $this;
    }

    protected $urlsLinked = true;

    private function setSafeMode(bool $safeMode)
    {
        $this->safeMode = (bool) $safeMode;

        return $this;
    }


    protected function lines(array $lines): ?string
    {
        $CurrentmarkdownBlock = null;

        foreach ($lines as $line) {

            if (chop($line) === '') {

                if (isset($CurrentmarkdownBlock)) {
                    $CurrentmarkdownBlock['interrupted'] = true;
                }

                continue;
            }

            if (strpos($line, "\t") !== false) {

                $parts = explode("\t", $line);

                $line = $parts[0];

                unset($parts[0]);

                foreach ($parts as $part) {
                    $shortage = 4 - mb_strlen($line, 'utf-8') % 4;

                    $line .= str_repeat(' ', $shortage);
                    $line .= $part;
                }
            }

            $indent = 0;

            while (isset($line[$indent]) and $line[$indent] === ' ') {
                $indent++;
            }

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($CurrentmarkdownBlock['continuable'])) {

                $markdownBlock = $this->{'block' . $CurrentmarkdownBlock['type'] . 'Continue'}($Line, $CurrentmarkdownBlock);

                if (isset($markdownBlock)) {

                    $CurrentmarkdownBlock = $markdownBlock;

                    continue;

                } else {

                    if ($this->ismarkdownBlockCompletable($CurrentmarkdownBlock['type'])) {
                        $CurrentmarkdownBlock = $this->{'block' . $CurrentmarkdownBlock['type'] . 'Complete'}($CurrentmarkdownBlock);
                    }
                }
            }

            $marker = $text[0];

            $blockTypes = $this->unmarkedmarkdownBlockTypes;

            if (isset($this->markdownBlockTypes[$marker])) {
                foreach ($this->markdownBlockTypes[$marker] as $blockType) {
                    $blockTypes[] = $blockType;
                }
            }


            foreach ($blockTypes as $blockType) {
                $markdownBlock = $this->{'block' . $blockType}($Line, $CurrentmarkdownBlock);

                if (isset($markdownBlock)) {
                    $markdownBlock['type'] = $blockType;

                    if (!isset($markdownBlock['identified'])) {
                        $markdownBlocks[] = $CurrentmarkdownBlock;

                        $markdownBlock['identified'] = true;
                    }

                    if ($this->ismarkdownBlockContinuable($blockType)) {
                        $markdownBlock['continuable'] = true;
                    }

                    $CurrentmarkdownBlock = $markdownBlock;

                    continue 2;
                }
            }

            # ~

            if (isset($CurrentmarkdownBlock) and !isset($CurrentmarkdownBlock['type']) and !isset($CurrentmarkdownBlock['interrupted'])) {

                $CurrentmarkdownBlock['element']['text'] .= "\n" . $text;

            } else {
                $markdownBlocks[] = $CurrentmarkdownBlock;

                $CurrentmarkdownBlock = $this->paragraph($Line);

                $CurrentmarkdownBlock['identified'] = true;
            }
        }

        # ~

        if (isset($CurrentmarkdownBlock['continuable']) and $this->ismarkdownBlockCompletable($CurrentmarkdownBlock['type'])) {
            $CurrentmarkdownBlock = $this->{'block' . $CurrentmarkdownBlock['type'] . 'Complete'}($CurrentmarkdownBlock);
        }

        # ~

        $markdownBlocks[] = $CurrentmarkdownBlock;

        unset($markdownBlocks[0]);

        # ~

        $markup = '';

        foreach ($markdownBlocks as $markdownBlock) {
            if (isset($markdownBlock['hidden'])) {
                continue;
            }

            $markup .= "\n";
            $markup .= isset($markdownBlock['markup']) ? $markdownBlock['markup'] : $this->element($markdownBlock['element']);
        }

        $markup .= "\n";

        # ~

        return $markup;
    }

    protected function ismarkdownBlockContinuable(string $Type): bool
    {
        return method_exists($this, 'block' . $Type . 'Continue');
    }

    protected function ismarkdownBlockCompletable(string $Type): bool
    {
        return method_exists($this, 'block' . $Type . 'Complete');
    }


    protected function blockCode(array $Line, ?array $markdownBlock = null): ?array
    {
        if (isset($markdownBlock) and !isset($markdownBlock['type']) and !isset($markdownBlock['interrupted'])) {
           return null;
        }

        if ($Line['indent'] >= 4) {

            $text = substr($Line['body'], 4);

            $markdownBlock = [
                'element' => [
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => [
                        'name' => 'code',
                        'text' => $text,
                    ],
                ],
            ];

            return $markdownBlock;
        }

        return null;
    }

    protected function blockCodeContinue(array $Line, array $markdownBlock)
    {
        if ($Line['indent'] >= 4) {
            
            if (isset($markdownBlock['interrupted'])) {
                $markdownBlock['element']['text']['text'] .= "\n";

                unset($markdownBlock['interrupted']);
            }

            $markdownBlock['element']['text']['text'] .= "\n";

            $text = substr($Line['body'], 4);

            $markdownBlock['element']['text']['text'] .= $text;

            return $markdownBlock;
        }
    }

    protected function blockCodeComplete(array $markdownBlock)
    {
        $text = $markdownBlock['element']['text']['text'];

        $markdownBlock['element']['text']['text'] = $text;

        return $markdownBlock;
    }

    /**
     * markdownBlock Comment
     * 
     * @param array $Line
     * 
     * @return mixed
     */
    protected function blockComment(array $Line)
    {
        if ($this->markupEscaped or $this->safeMode) {
           return null;
        }

        if (
            isset($Line['text'][3]) &&
            $Line['text'][3] === '-' &&
            $Line['text'][2] === '-' &&
            $Line['text'][1] === '!'
        ) {

            $markdownBlock = [
                'markup' => $Line['body'],
            ];

            if (preg_match('/-->$/', $Line['text'])) {
                $markdownBlock['closed'] = true;
            }

            return $markdownBlock;
        }
    }

    protected function blockCommentContinue(array $Line, array $markdownBlock): ?array
    {
        if (isset($markdownBlock['closed'])) {
           return null;
        }

        $markdownBlock['markup'] .= "\n" . $Line['body'];

        if (preg_match('/-->$/', $Line['text'])) {
            $markdownBlock['closed'] = true;
        }

        return $markdownBlock;
    }


    /**
     * Fenced Codes
     * 
     * @param array $Line
     * 
     * @return mixed
     */
    protected function blockFencedCode(array $Line): ?array
    {
        if (preg_match('/^[' . $Line['text'][0] . ']{3,}[ ]*([^`]+)?[ ]*$/', $Line['text'], $matches)) {

            $markdownElement = [
                'name' => 'code',
                'text' => '',
            ];

            if (isset($matches[1])) {
                /**
                 * https://www.w3.org/TR/2011/WD-html5-20110525/elements.html#classes
                 * Every HTML element may have a class attribute specified.
                 * The attribute, if specified, must have a value that is a set
                 * of space-separated tokens representing the various classes
                 * that the element belongs to.
                 * [...]
                 * The space characters, for the purposes of this specification,
                 * are U+0020 SPACE, U+0009 CHARACTER TABULATION (tab),
                 * U+000A LINE FEED (LF), U+000C FORM FEED (FF), and
                 * U+000D CARRIAGE RETURN (CR).
                 */
                $language = substr($matches[1], 0, strcspn($matches[1], " \t\n\f\r"));

                $class = 'language-' . $language;

                $markdownElement['attributes'] = [
                    'class' => $class,
                ];
            }

            $markdownBlock = [
                'char' => $Line['text'][0],
                'element' => [
                    'name' => 'pre',
                    'handler' => 'element',
                    'text' => $markdownElement,
                ],
            ];

            return $markdownBlock;
        }

        return null;
    }

    protected function blockFencedCodeContinue(array $Line, array $markdownBlock): ?array
    {
        if (isset($markdownBlock['complete'])) {
           return null;
        }

        if (isset($markdownBlock['interrupted'])) {
            $markdownBlock['element']['text']['text'] .= "\n";

            unset($markdownBlock['interrupted']);
        }

        if (preg_match('/^' . $markdownBlock['char'] . '{3,}[ ]*$/', $Line['text'])) {
            $markdownBlock['element']['text']['text'] = substr($markdownBlock['element']['text']['text'], 1);

            $markdownBlock['complete'] = true;

            return $markdownBlock;
        }

        $markdownBlock['element']['text']['text'] .= "\n" . $Line['body'];

        return $markdownBlock;
    }


    protected function blockFencedCodeComplete(array $markdownBlock): ?array
    {
        $text = $markdownBlock['element']['text']['text'];

        $markdownBlock['element']['text']['text'] = $text;

        return $markdownBlock;
    }

    #
    # Header
    /**
     * markdownBlock Header
     * 
     * @param array $Line
     * 
     * @return ?array
     */
    protected function blockHeader($Line): ?array
    {
        if (isset($Line['text'][1])) {
            $level = 1;

            while (isset($Line['text'][$level]) && $Line['text'][$level] === '#') {
                $level++;
            }

            if ($level > 6) {
               return null;
            }

            $text = trim($Line['text'], '# ');

            $markdownBlock = array(
                'element' => array(
                    'name' => 'h' . min(6, $level),
                    'text' => $text,
                    'handler' => 'line',
                ),
            );

            return $markdownBlock;
        }

        return null;
    }

    #
    # List

    protected function blockList(array $Line)
    {
        [$name, $pattern] = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]+[.]');

        if (preg_match('/^(' . $pattern . '[ ]+)(.*)/', $Line['text'], $matches)) {
            $markdownBlock = array(
                'indent' => $Line['indent'],
                'pattern' => $pattern,
                'element' => array(
                    'name' => $name,
                    'handler' => 'elements',
                ),
            );

            if ($name === 'ol') {
                $listStart = stristr($matches[0], '.', true);

                if ($listStart !== '1') {
                    $markdownBlock['element']['attributes'] = array('start' => $listStart);
                }
            }

            $markdownBlock['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $matches[2],
                ),
            );

            $markdownBlock['element']['text'][] = &$markdownBlock['li'];

            return $markdownBlock;
        }
    }

    protected function blockListContinue(array $Line, array $markdownBlock)
    {
        if ($markdownBlock['indent'] === $Line['indent'] and preg_match('/^' . $markdownBlock['pattern'] . '(?:[ ]+(.*)|$)/', $Line['text'], $matches)) {
            if (isset($markdownBlock['interrupted'])) {
                $markdownBlock['li']['text'][] = '';

                $markdownBlock['loose'] = true;

                unset($markdownBlock['interrupted']);
            }

            unset($markdownBlock['li']);

            $text = isset($matches[1]) ? $matches[1] : '';

            $markdownBlock['li'] = array(
                'name' => 'li',
                'handler' => 'li',
                'text' => array(
                    $text,
                ),
            );

            $markdownBlock['element']['text'][] = &$markdownBlock['li'];

            return $markdownBlock;
        }

        if ($Line['text'][0] === '[' and $this->blockReference($Line)) {
            return $markdownBlock;
        }

        if (!isset($markdownBlock['interrupted'])) {
            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

            $markdownBlock['li']['text'][] = $text;

            return $markdownBlock;
        }

        if ($Line['indent'] > 0) {
            $markdownBlock['li']['text'][] = '';

            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

            $markdownBlock['li']['text'][] = $text;

            unset($markdownBlock['interrupted']);

            return $markdownBlock;
        }

        return null;
    }

    protected function blockListComplete(array $markdownBlock)
    {
        if (isset($markdownBlock['loose'])) {
            foreach ($markdownBlock['element']['text'] as &$li) {
                if (end($li['text']) !== '') {
                    $li['text'][] = '';
                }
            }
        }

        return $markdownBlock;
    }




    protected function blockQuote(array $Line): ?array
    {
        if (preg_match('/^>[ ]?(.*)/', $Line['text'], $matches)) {

            $markdownBlock = array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => 'lines',
                    'text' => (array) $matches[1],
                ),
            );

            return $markdownBlock;
        }

        return null;
    }



    protected function blockQuoteContinue(array $Line, array $markdownBlock): ?array
    {
        if ($Line['text'][0] === '>' and preg_match('/^>[ ]?(.*)/', $Line['text'], $matches)) {

            if (isset($markdownBlock['interrupted'])) {

                $markdownBlock['element']['text'][] = '';

                unset($markdownBlock['interrupted']);
            }

            $markdownBlock['element']['text'][] = $matches[1];

            return $markdownBlock;
        }

        if (!isset($markdownBlock['interrupted'])) {

            $markdownBlock['element']['text'][] = $Line['text'];

            return $markdownBlock;
        }

        return null;
    }



    protected function blockRule(array $Line): ?array
    {
        if (preg_match('/^([' . $Line['text'][0] . '])([ ]*\1){2,}[ ]*$/', $Line['text'])) {

            $markdownBlock = array(
                'element' => array(
                    'name' => 'hr'
                ),
            );

            return $markdownBlock;
        }

        return null;
    }

    #
    # Setext

    protected function blockSetextHeader(array $Line, array $markdownBlock = null): ?array
    {
        if (!isset($markdownBlock) or isset($markdownBlock['type']) or isset($markdownBlock['interrupted'])) {
           return null;
        }

        if (chop($Line['text'], $Line['text'][0]) === '') {

            $markdownBlock['element']['name'] = $Line['text'][0] === '=' ? 'h1' : 'h2';

            return $markdownBlock;
        }

        return null;
    }



    protected function blockMarkup(array $Line): ?array
    {
        if ($this->markupEscaped or $this->safeMode) {
           return null;
        }

        if (preg_match('/^<(\w[\w-]*)(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*(\/)?>/', $Line['text'], $matches)) {

            $element = strtolower($matches[1]);

            if (in_array($element, $this->textLevelmarkdownElements)) {
               return null;
            }

            $markdownBlock = array(
                'name' => $matches[1],
                'depth' => 0,
                'markup' => $Line['text'],
            );

            $length = strlen($matches[0]);

            $remainder = substr($Line['text'], $length);

            if (trim($remainder) === '') {

                if (isset($matches[2]) or in_array($matches[1], $this->voidmarkdownElements)) {
                    $markdownBlock['closed'] = true;

                    $markdownBlock['void'] = true;
                }

            } else {
                if (isset($matches[2]) or in_array($matches[1], $this->voidmarkdownElements)) {
                   return null;
                }

                if (preg_match('/<\/' . $matches[1] . '>[ ]*$/i', $remainder)) {
                    $markdownBlock['closed'] = true;
                }
            }

            return $markdownBlock;
        }

        return null;
    }



    protected function blockMarkupContinue(array $Line, array $markdownBlock): ?array
    {
        if (isset($markdownBlock['closed'])) {
           return null;
        }

        if (preg_match('/^<' . $markdownBlock['name'] . '(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*>/i', $Line['text'])) # open
        {
            $markdownBlock['depth']++;
        }

        if (preg_match('/(.*?)<\/' . $markdownBlock['name'] . '>[ ]*$/i', $Line['text'], $matches)) # close
        {
            if ($markdownBlock['depth'] > 0) {
                $markdownBlock['depth']--;
            } else {
                $markdownBlock['closed'] = true;
            }
        }

        if (isset($markdownBlock['interrupted'])) {
            $markdownBlock['markup'] .= "\n";

            unset($markdownBlock['interrupted']);
        }

        $markdownBlock['markup'] .= "\n" . $Line['body'];

        return $markdownBlock;
    }




    protected function blockReference(array $Line): ?array
    {
        if (preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*$/', $Line['text'], $matches)) {

            $id = strtolower($matches[1]);

            $Data = array(
                'url' => $matches[2],
                'title' => null,
            );

            if (isset($matches[3])) {
                $Data['title'] = $matches[3];
            }

            $this->DefinitionData['Reference'][$id] = $Data;

            $markdownBlock = array(
                'hidden' => true,
            );

            return $markdownBlock;
        }

        return null;
    }

    #
    # Table

    protected function blockTable($Line, array $markdownBlock = null)
    {
        if (!isset($markdownBlock) or isset($markdownBlock['type']) or isset($markdownBlock['interrupted'])) {
           return null;
        }

        if (strpos($markdownBlock['element']['text'], '|') !== false and chop($Line['text'], ' -:|') === '') {
            $alignments = array();

            $divider = $Line['text'];

            $divider = trim($divider);
            $divider = trim($divider, '|');

            $dividerCells = explode('|', $divider);

            foreach ($dividerCells as $dividerCell) {
                $dividerCell = trim($dividerCell);

                if ($dividerCell === '') {
                    continue;
                }

                $alignment = null;

                if ($dividerCell[0] === ':') {
                    $alignment = 'left';
                }

                if (substr($dividerCell, -1) === ':') {
                    $alignment = $alignment === 'left' ? 'center' : 'right';
                }

                $alignments[] = $alignment;
            }

            # ~

            $HeadermarkdownElements = array();

            $header = $markdownBlock['element']['text'];

            $header = trim($header);
            $header = trim($header, '|');

            $headerCells = explode('|', $header);

            foreach ($headerCells as $index => $headerCell) {
                $headerCell = trim($headerCell);

                $HeadermarkdownElement = array(
                    'name' => 'th',
                    'text' => $headerCell,
                    'handler' => 'line',
                );

                if (isset($alignments[$index])) {
                    $alignment = $alignments[$index];

                    $HeadermarkdownElement['attributes'] = array(
                        'style' => 'text-align: ' . $alignment . ';',
                    );
                }

                $HeadermarkdownElements[] = $HeadermarkdownElement;
            }

            # ~

            $markdownBlock = array(
                'alignments' => $alignments,
                'identified' => true,
                'element' => array(
                    'name' => 'table',
                    'handler' => 'elements',
                ),
            );

            $markdownBlock['element']['text'][] = array(
                'name' => 'thead',
                'handler' => 'elements',
            );

            $markdownBlock['element']['text'][] = array(
                'name' => 'tbody',
                'handler' => 'elements',
                'text' => array(),
            );

            $markdownBlock['element']['text'][0]['text'][] = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $HeadermarkdownElements,
            );

            return $markdownBlock;
        }

        return null;
    }

    protected function blockTableContinue($Line, array $markdownBlock)
    {
        if (isset($markdownBlock['interrupted'])) {
           return null;
        }

        if ($Line['text'][0] === '|' or strpos($Line['text'], '|')) {
            $markdownElements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/', $row, $matches);

            foreach ($matches[0] as $index => $cell) {
                $cell = trim($cell);

                $markdownElement = array(
                    'name' => 'td',
                    'handler' => 'line',
                    'text' => $cell,
                );

                if (isset($markdownBlock['alignments'][$index])) {
                    $markdownElement['attributes'] = array(
                        'style' => 'text-align: ' . $markdownBlock['alignments'][$index] . ';',
                    );
                }

                $markdownElements[] = $markdownElement;
            }

            $markdownElement = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $markdownElements,
            );

            $markdownBlock['element']['text'][1]['text'][] = $markdownElement;

            return $markdownBlock;
        }
    }

    #
    # ~
    #

    protected function paragraph($Line)
    {
        $markdownBlock = array(
            'element' => array(
                'name' => 'p',
                'text' => $Line['text'],
                'handler' => 'line',
            ),
        );

        return $markdownBlock;
    }

    /**
     * Inline markdownElement
     * 
     * @param string $text
     * @param array $nonNestables
     * 
     */
    public function line(string $text, array $nonNestables = array())
    {
        $markup = '';

        # $excerpt is based on the first occurrence of a marker

        while ($excerpt = strpbrk($text, $this->inlineMarkerList)) {

            $marker = $excerpt[0];

            $markerPosition = strpos($text, $marker);

            $Excerpt = array('text' => $excerpt, 'context' => $text);

            foreach ($this->InlineTypes[$marker] as $inlineType) {
                # check to see if the current inline type is nestable in the current context

                if (!empty($nonNestables) && in_array($inlineType, $nonNestables)) {
                    continue;
                }

                $Inline = $this->{'inline' . $inlineType}($Excerpt);

                if (!isset($Inline)) {
                    continue;
                }

                # makes sure that the inline belongs to "our" marker

                if (isset($Inline['position']) and $Inline['position'] > $markerPosition) {
                    continue;
                }

                # sets a default inline position

                if (!isset($Inline['position'])) {
                    $Inline['position'] = $markerPosition;
                }

                # cause the new element to 'inherit' our non nestables
                foreach ($nonNestables as $non_nestable) {
                    $Inline['element']['nonNestables'][] = $non_nestable;
                }

                # the text that comes before the inline
                $unmarkedText = substr($text, 0, $Inline['position']);

                # compile the unmarked text
                $markup .= $this->unmarkedText($unmarkedText);

                # compile the inline
                $markup .= isset($Inline['markup']) ? $Inline['markup'] : $this->element($Inline['element']);

                # remove the examined text
                $text = substr($text, $Inline['position'] + $Inline['extent']);

                continue 2;
            }

            # the marker does not belong to an inline

            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $markup .= $this->unmarkedText($unmarkedText);

            $text = substr($text, $markerPosition + 1);
        }

        $markup .= $this->unmarkedText($text);

        return $markup;
    }

    #
    # ~
    #

    protected function inlineCode($Excerpt)
    {
        $marker = $Excerpt['text'][0];

        if (preg_match('/^(' . $marker . '+)[ ]*(.+?)[ ]*(?<!' . $marker . ')\1(?!' . $marker . ')/s', $Excerpt['text'], $matches)) {
            $text = $matches[2];
            $text = preg_replace("/[ ]*\n/", ' ', $text);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            );
        }
    }

    protected function inlineEmailTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $Excerpt['text'], $matches)) {
            $url = $matches[1];

            if (!isset($matches[2])) {
                $url = 'mailto:' . $url;
            }

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[1],
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    protected function inlineEmphasis(array $Excerpt): ?array
    {
        if (!isset($Excerpt['text'][1])) {
           return null;
        }

        $marker = $Excerpt['text'][0];

        if ($Excerpt['text'][1] === $marker and preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'strong';
        } elseif (preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches)) {
            $emphasis = 'i';
        } else {
           return null;
        }

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => 'line',
                'text' => $matches[1],
            ),
        );
    }

    protected function inlineEscapeSequence(array $Excerpt)
    {
        if (isset($Excerpt['text'][1]) and in_array($Excerpt['text'][1], $this->specialCharacters)) {
            return array(
                'markup' => $Excerpt['text'][1],
                'extent' => 2,
            );
        }
    }

    protected function inlineImage(array $Excerpt)
    {
        if (!isset($Excerpt['text'][1]) or $Excerpt['text'][1] !== '[') {
           return null;
        }

        $Excerpt['text'] = substr($Excerpt['text'], 1);

        $Link = $this->inlineLink($Excerpt);

        if ($Link === null) {
           return null;
        }

        $Inline = array(
            'extent' => $Link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'attributes' => array(
                    'src' => $Link['element']['attributes']['href'],
                    'alt' => $Link['element']['text'],
                ),
            ),
        );

        $Inline['element']['attributes'] += $Link['element']['attributes'];

        unset($Inline['element']['attributes']['href']);

        return $Inline;
    }

    protected function inlineLink(array $Excerpt)
    {
        $markdownElement = array(
            'name' => 'a',
            'handler' => 'line',
            'nonNestables' => array('Url', 'Link'),
            'text' => null,
            'attributes' => array(
                'href' => null,
                'title' => null,
            ),
        );

        $extent = 0;

        $remainder = $Excerpt['text'];

        if (preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches)) {
            $markdownElement['text'] = $matches[1];

            $extent += strlen($matches[0]);

            $remainder = substr($remainder, $extent);
        } else {
           return null;
        }

        if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*"|\'[^\']*\'))?\s*[)]/', $remainder, $matches)) {
            $markdownElement['attributes']['href'] = $matches[1];

            if (isset($matches[2])) {
                $markdownElement['attributes']['title'] = substr($matches[2], 1, -1);
            }

            $extent += strlen($matches[0]);
        } else {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches)) {
                $definition = strlen($matches[1]) ? $matches[1] : $markdownElement['text'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            } else {
                $definition = strtolower($markdownElement['text']);
            }

            if (!isset($this->DefinitionData['Reference'][$definition])) {
               return null;
            }

            $Definition = $this->DefinitionData['Reference'][$definition];

            $markdownElement['attributes']['href'] = $Definition['url'];
            $markdownElement['attributes']['title'] = $Definition['title'];
        }

        return array(
            'extent' => $extent,
            'element' => $markdownElement,
        );
    }

    protected function inlineMarkup($Excerpt)
    {
        if ($this->markupEscaped or $this->safeMode or strpos($Excerpt['text'], '>') === false) {
           return null;
        }

        if ($Excerpt['text'][1] === '/' and preg_match('/^<\/\w[\w-]*[ ]*>/s', $Excerpt['text'], $matches)) {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }

        if ($Excerpt['text'][1] === '!' and preg_match('/^<!---?[^>-](?:-?[^-])*-->/s', $Excerpt['text'], $matches)) {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }

        if ($Excerpt['text'][1] !== ' ' and preg_match('/^<\w[\w-]*(?:[ ]*' . $this->regexHtmlAttribute . ')*[ ]*\/?>/s', $Excerpt['text'], $matches)) {
            return array(
                'markup' => $matches[0],
                'extent' => strlen($matches[0]),
            );
        }
    }

    protected function inlineSpecialCharacter($Excerpt)
    {
        if ($Excerpt['text'][0] === '&' and !preg_match('/^&#?\w+;/', $Excerpt['text'])) {
            return array(
                'markup' => '&amp;',
                'extent' => 1,
            );
        }

        $SpecialCharacter = array('>' => 'gt', '<' => 'lt', '"' => 'quot');

        if (isset($SpecialCharacter[$Excerpt['text'][0]])) {
            return array(
                'markup' => '&' . $SpecialCharacter[$Excerpt['text'][0]] . ';',
                'extent' => 1,
            );
        }
    }

    protected function inlineStrikethrough($Excerpt)
    {
        if (!isset($Excerpt['text'][1])) {
           return null;
        }

        if ($Excerpt['text'][1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $Excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'del',
                    'text' => $matches[1],
                    'handler' => 'line',
                ),
            );
        }
    }

    protected function inlineUrl($Excerpt)
    {
        if ($this->urlsLinked !== true or !isset($Excerpt['text'][2]) or $Excerpt['text'][2] !== '/') {
           return null;
        }

        if (preg_match('/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui', $Excerpt['context'], $matches, PREG_OFFSET_CAPTURE)) {
            $url = $matches[0][0];

            $Inline = array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );

            return $Inline;
        }
    }

    protected function inlineUrlTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<(\w+:\/{2}[^ >]+)>/i', $Excerpt['text'], $matches)) {
            $url = $matches[1];

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                    ),
                ),
            );
        }
    }

    # ~

    protected function unmarkedText($text)
    {
        if ($this->breaksEnabled) {

            $text = preg_replace('/[ ]*\n/', "<br />\n", $text);

        } else {
            $text = preg_replace('/(?:[ ][ ]+|[ ]*\\\\)\n/', "<br />\n", $text);
            $text = str_replace(" \n", "\n", $text);
        }

        return $text;
    }

    #
    # Handlers
    #

    protected function element(array $markdownElement)
    {
        if ($this->safeMode) {
            $markdownElement = $this->sanitisemarkdownElement($markdownElement);
        }

        $markup = '<' . $markdownElement['name'];

        if (isset($markdownElement['attributes'])) {
            foreach ($markdownElement['attributes'] as $name => $value) {
                if ($value === null) {
                    continue;
                }

                $markup .= ' ' . $name . '="' . self::escape($value) . '"';
            }
        }

        $permitRawHtml = false;

        if (isset($markdownElement['text'])) {
            $text = $markdownElement['text'];
        }
        // very strongly consider an alternative if you're writing an
        // extension
        elseif (isset($markdownElement['rawHtml'])) {
            $text = $markdownElement['rawHtml'];
            $allowRawHtmlInSafeMode = isset($markdownElement['allowRawHtmlInSafeMode']) && $markdownElement['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }

        if (isset($text)) {
            $markup .= '>';

            if (!isset($markdownElement['nonNestables'])) {
                $markdownElement['nonNestables'] = array();
            }

            if (isset($markdownElement['handler'])) {
                $markup .= $this->{$markdownElement['handler']}($text, $markdownElement['nonNestables']);
            } elseif (!$permitRawHtml) {
                $markup .= self::escape($text, true);
            } else {
                $markup .= $text;
            }

            $markup .= '</' . $markdownElement['name'] . '>';
        } else {
            $markup .= ' />';
        }

        return $markup;
    }

    protected function elements(array $markdownElements)
    {
        $markup = '';

        foreach ($markdownElements as $markdownElement) {
            $markup .= "\n" . $this->element($markdownElement);
        }

        $markup .= "\n";

        return $markup;
    }

    # ~

    protected function li($lines): ?string
    {
        $markup = $this->lines($lines);

        $trimmedMarkup = trim($markup);

        if (!in_array('', $lines) and substr($trimmedMarkup, 0, 3) === '<p>') {
            $markup = $trimmedMarkup;
            $markup = substr($markup, 3);

            $position = strpos($markup, "</p>");

            $markup = substr_replace($markup, '', $position, 4);
        }

        return $markup;
    }

    #
    # Deprecated Methods
    #

    function parse($text)
    {
        $markup = $this->markdown_text($text);

        return $markup;
    }


    protected function sanitisemarkdownElement(array $markdownElement)
    {
        static $goodAttribute = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
        static $safeUrlNameToAtt = array(
        'a' => 'href',
        'img' => 'src',
        );

        if (isset($safeUrlNameToAtt[$markdownElement['name']])) {
            $markdownElement = $this->filterUnsafeUrlInAttribute($markdownElement, $safeUrlNameToAtt[$markdownElement['name']]);
        }

        if (!empty($markdownElement['attributes'])) {

            foreach ($markdownElement['attributes'] as $att => $val) {
                # filter out badly parsed attribute
                if (!preg_match($goodAttribute, $att)) {
                    unset($markdownElement['attributes'][$att]);
                }
                # dump onevent attribute
                elseif (self::striAtStart($att, 'on')) {
                    unset($markdownElement['attributes'][$att]);
                }
            }
        }

        return $markdownElement;
    }

    protected function filterUnsafeUrlInAttribute(array $markdownElement, $attribute)
    {
        foreach ($this->safeLinksWhitelist as $scheme) {

            if (self::striAtStart($markdownElement['attributes'][$attribute], $scheme)) {
                return $markdownElement;
            }
        }

        $markdownElement['attributes'][$attribute] = str_replace(':', '%3A', $markdownElement['attributes'][$attribute]);

        return $markdownElement;
    }

    #
    # Static Methods
    #

    protected static function escape($text, $allowQuotes = false)
    {
        return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

    protected static function striAtStart($string, $needle)
    {
        $len = strlen($needle);

        if ($len > strlen($string)) {
            return false;
        } else {
            return strtolower(substr($string, 0, $len)) === strtolower($needle);
        }
    }

    protected static function instance($name = 'default')
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }

        $instance = new static();

        self::$instances[$name] = $instance;

        return $instance;
    }

    
}