<?php

declare(strict_types=1);

namespace FastVolt\Helper;

#
#
# Parsedown Extra
# https://github.com/erusev/parsedown-extra
#
# (c) Emanuil Rusev
# http://erusev.com
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

class ParsedownExtra extends Markdown\Process\ParseMarkdown
{
    private const version = '0.8.0';

    private array $BlockTypes;

    private array $textLevelElements;

    private string $textElements;

    private array $voidElements;

    protected string $regexAttribute = '(?:[#.][-\w]+[ ]*)';



    public function __construct()
    {
        $this->BlockTypes[':'] [] = 'DefinitionList';
        $this->BlockTypes['*'] [] = 'Abbreviation';

        # identify footnote definitions before reference definitions
        array_unshift($this->BlockTypes['['], 'Footnote');

        # identify footnote markers before before links
        array_unshift($this->InlineTypes['['], 'FootnoteMarker');
    }


    /**
     * Convert to HTML
     *
     * @param string $text
     *
     * @return ?string
     */
    public function advanced_markdown_text(string $text): ?string
    {
        $markdownElements = $this->markdown_text($text);

        # convert to markup
        $markup = $this->elements([$markdownElements]);

        # trim line breaks
        $markup = trim($markup, "\n");

        # merge consecutive dl elements
        $markup = preg_replace('/<\/dl>\s+<dl>\s+/', '', $markup);

        # add footnotes
        if (isset($this->DefinitionData['Footnote'])) {
            $Element = $this->buildFootnoteElement();

            $markup .= "\n" . $this->element($Element);
        }

        return $markup;
    }

    /**
     * Block Abbrev
     *
     * @param array $Line
     *
     * @return ?array
     */
    protected function blockAbbreviation(array $Line): ?array
    {
        if (preg_match('/^\*\[(.+?)\]:[ ]*(.+?)[ ]*$/', $Line['text'], $matches)) {
            $this->DefinitionData['Abbreviation'][$matches[1]] = $matches[2];

            $Block = array(
                'hidden' => true,
            );

            return $Block;
        }

        return null;
    }


    /**
     * Block Footnote
     *
     * @param array $Line
     *
     * @return array
     */
    protected function blockFootnote(array $Line): ?array
    {
        if (preg_match('/^\[\^(.+?)\]:[ ]?(.*)$/', $Line['text'], $matches)) {
            $Block = array(
                'label' => $matches[1],
                'text' => $matches[2],
                'hidden' => true,
            );

            return $Block;
        }

        return null;
    }


    /**
     * Block Footnote (Cont.)
     *
     * @param array $Line
     *
     * @return ?array<string, mixed>
     */
    protected function blockFootnoteContinue(array $Line, array $Block): mixed
    {
        if ($Line['text'][0] === '[' and preg_match('/^\[\^(.+?)\]:/', $Line['text'])) {
            return null;
        }

        if (isset($Block['interrupted'])) {

            if ($Line['indent'] >= 4) {

                $Block['text'] .= "\n\n" . $Line['text'];

                return $Block;
            }

        } else {

            $Block['text'] .= "\n" . $Line['text'];

            return $Block;
        }

        return null;
    }


    protected function blockFootnoteComplete(array $Block): array
    {
        $this->DefinitionData['Footnote'][$Block['label']] = array(
            'text' => $Block['text'],
            'count' => null,
            'number' => null,
        );

        return $Block;
    }


    /**
     * Block Definition Lists
     *
     * @param array $Line
     *
     * @return ?array
     */
    protected function blockDefinitionList(array $Line, array $Block): ?array
    {
        if (!isset($Block) or $Block['type'] !== 'Paragraph') {
            return null;
        }

        $Element = array(
            'name' => 'dl',
            'elements' => array(),
        );

        $terms = explode("\n", $Block['element']['handler']['argument']);

        foreach ($terms as $term) {

            $Element['elements'] [] = array(
                'name' => 'dt',
                'handler' => array(
                    'function' => 'lineElements',
                    'argument' => $term,
                    'destination' => 'elements'
                ),
            );
        }

        $Block['element'] = $Element;

        $Block = $this->addDdElement($Line, $Block);

        return $Block;
    }



    protected function blockDefinitionListContinue($Line, array $Block): ?array
    {
        if ($Line['text'][0] === ':') {

            $Block = $this->addDdElement($Line, $Block);

            return $Block;

        } else {

            if (isset($Block['interrupted']) and $Line['indent'] === 0) {
                return null;
            }

            if (isset($Block['interrupted'])) {

                $Block['dd']['handler']['function'] = 'textElements';
                $Block['dd']['handler']['argument'] .= "\n\n";

                $Block['dd']['handler']['destination'] = 'elements';

                unset($Block['interrupted']);
            }

            $text = substr($Line['body'], min($Line['indent'], 4));

            $Block['dd']['handler']['argument'] .= "\n" . $text;

            return $Block;
        }
    }



    /**
     * Block Header
     *
     * @param array $Line
     *
     * @return ?array
     */
    protected function blockHeader(array $Line): ?array
    {
        $Block = parent::blockHeader($Line);

        if ($Block !== null && preg_match('/[ #]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        return $Block;
    }



    /**
     * Block Markup
     *
     * @param array $Line
     */
    protected function blockMarkup(array $Line): array|null
    {
        if ($this->markupEscaped or $this->safeMode) {
            return null;
        }

        if (preg_match('/^<(\w[\w-]*)(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*(\/)?>/', $Line['text'], $matches)) {
            $element = strtolower($matches[1]);

            if (in_array($element, $this->textLevelElements)) {
                return null;
            }

            $Block = array(
                'name' => $matches[1],
                'depth' => 0,
                'element' => array(
                    'rawHtml' => $Line['text'],
                    'autobreak' => true,
                ),
            );

            $length = strlen($matches[0]);
            $remainder = substr($Line['text'], $length);

            if (trim($remainder) === '') {
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements)) {
                    $Block['closed'] = true;
                    $Block['void'] = true;
                }
            } else {
                if (isset($matches[2]) or in_array($matches[1], $this->voidElements)) {
                    return null;
                }
                if (preg_match('/<\/'.$matches[1].'>[ ]*$/i', $remainder)) {
                    $Block['closed'] = true;
                }
            }

            return $Block;
        }

        return null;
    }



    protected function blockMarkupContinue(array $Line, array $Block): ?array
    {
        if (isset($Block['closed'])) {
            return null;
        }

        if (preg_match('/^<'.$Block['name'].'(?:[ ]*'.$this->regexHtmlAttribute.')*[ ]*>/i', $Line['text'])) { # open
            $Block['depth']++;
        }

        if (preg_match('/(.*?)<\/'.$Block['name'].'>[ ]*$/i', $Line['text'], $matches)) { # close
            if ($Block['depth'] > 0) {
                $Block['depth']--;
            } else {
                $Block['closed'] = true;
            }
        }

        if (isset($Block['interrupted'])) {
            $Block['element']['rawHtml'] .= "\n";
            unset($Block['interrupted']);
        }

        $Block['element']['rawHtml'] .= "\n".$Line['body'];

        return $Block;
    }

    protected function blockMarkupComplete($Block)
    {
        if (!isset($Block['void'])) {
            $Block['element']['rawHtml'] = $this->processTag($Block['element']['rawHtml']);
        }

        return $Block;
    }

    #
    # Setext

    protected function blockSetextHeader($Line, array $Block = null)
    {
        $Block = parent::blockSetextHeader($Line, $Block);

        if ($Block !== null && preg_match('/[ ]*{('.$this->regexAttribute.'+)}[ ]*$/', $Block['element']['handler']['argument'], $matches, PREG_OFFSET_CAPTURE)) {
            $attributeString = $matches[1][0];

            $Block['element']['attributes'] = $this->parseAttributeData($attributeString);

            $Block['element']['handler']['argument'] = substr($Block['element']['handler']['argument'], 0, $matches[0][1]);
        }

        return $Block;
    }

    #
    # Inline Elements
    #

    #
    # Footnote Marker

    protected function inlineFootnoteMarker($Excerpt)
    {
        if (preg_match('/^\[\^(.+?)\]/', $Excerpt['text'], $matches)) {
            $name = $matches[1];

            if (!isset($this->DefinitionData['Footnote'][$name])) {
                return null;
            }

            $this->DefinitionData['Footnote'][$name]['count']++;

            if (!isset($this->DefinitionData['Footnote'][$name]['number'])) {
                $this->DefinitionData['Footnote'][$name]['number'] = ++$this->footnoteCount; # » &
            }

            $Element = array(
                'name' => 'sup',
                'attributes' => array('id' => 'fnref'.$this->DefinitionData['Footnote'][$name]['count'].':'.$name),
                'element' => array(
                    'name' => 'a',
                    'attributes' => array('href' => '#fn:'.$name, 'class' => 'footnote-ref'),
                    'text' => $this->DefinitionData['Footnote'][$name]['number'],
                ),
            );

            return array(
                'extent' => strlen($matches[0]),
                'element' => $Element,
            );
        }
    }

    private $footnoteCount = 0;

    #
    # Link

    protected function inlineLink($Excerpt)
    {
        $Link = parent::inlineLink($Excerpt);

        $remainder = $Link !== null ? substr($Excerpt['text'], $Link['extent']) : '';

        if (preg_match('/^[ ]*{('.$this->regexAttribute.'+)}/', $remainder, $matches)) {
            $Link['element']['attributes'] += $this->parseAttributeData($matches[1]);

            $Link['extent'] += strlen($matches[0]);
        }

        return $Link;
    }

    #
    # ~
    #

    private $currentAbreviation;
    private $currentMeaning;

    protected function insertAbreviation(array $Element)
    {
        if (isset($Element['text'])) {
            $Element['elements'] = self::pregReplaceElements(
                '/\b'.preg_quote($this->currentAbreviation, '/').'\b/',
                array(
                    array(
                        'name' => 'abbr',
                        'attributes' => array(
                            'title' => $this->currentMeaning,
                        ),
                        'text' => $this->currentAbreviation,
                    )
                ),
                $Element['text']
            );

            unset($Element['text']);
        }

        return $Element;
    }

    protected function inlineText($text)
    {
        $Inline = parent::inlineText($text);

        if (isset($this->DefinitionData['Abbreviation'])) {
            foreach ($this->DefinitionData['Abbreviation'] as $abbreviation => $meaning) {
                $this->currentAbreviation = $abbreviation;
                $this->currentMeaning = $meaning;

                $Inline['element'] = $this->elementApplyRecursiveDepthFirst(
                    array($this, 'insertAbreviation'),
                    $Inline['element']
                );
            }
        }

        return $Inline;
    }

    #
    # Util Methods
    #

    protected function addDdElement(array $Line, array $Block)
    {
        $text = substr($Line['text'], 1);
        $text = trim($text);

        unset($Block['dd']);

        $Block['dd'] = array(
            'name' => 'dd',
            'handler' => array(
                'function' => 'lineElements',
                'argument' => $text,
                'destination' => 'elements'
            ),
        );

        if (isset($Block['interrupted'])) {
            $Block['dd']['handler']['function'] = 'textElements';

            unset($Block['interrupted']);
        }

        $Block['element']['elements'] [] = &$Block['dd'];

        return $Block;
    }

    protected function buildFootnoteElement()
    {
        $Element = array(
            'name' => 'div',
            'attributes' => array('class' => 'footnotes'),
            'elements' => array(
                array('name' => 'hr'),
                array(
                    'name' => 'ol',
                    'elements' => array(),
                ),
            ),
        );

        uasort($this->DefinitionData['Footnote'], 'self::sortFootnotes');

        foreach ($this->DefinitionData['Footnote'] as $definitionId => $DefinitionData) {
            if (!isset($DefinitionData['number'])) {
                continue;
            }

            $text = $DefinitionData['text'];

            $textElements = parent::textElements($text);

            $numbers = range(1, $DefinitionData['count']);

            $backLinkElements = array();

            foreach ($numbers as $number) {
                $backLinkElements[] = array('text' => ' ');
                $backLinkElements[] = array(
                    'name' => 'a',
                    'attributes' => array(
                        'href' => "#fnref$number:$definitionId",
                        'rev' => 'footnote',
                        'class' => 'footnote-backref',
                    ),
                    'rawHtml' => '&#8617;',
                    'allowRawHtmlInSafeMode' => true,
                    'autobreak' => false,
                );
            }

            unset($backLinkElements[0]);

            $n = count($textElements) - 1;

            if ($textElements[$n]['name'] === 'p') {
                $backLinkElements = array_merge(
                    array(
                        array(
                            'rawHtml' => '&#160;',
                            'allowRawHtmlInSafeMode' => true,
                        ),
                    ),
                    $backLinkElements
                );

                unset($textElements[$n]['name']);

                $textElements[$n] = array(
                    'name' => 'p',
                    'elements' => array_merge(
                        array($textElements[$n]),
                        $backLinkElements
                    ),
                );
            } else {
                $textElements[] = array(
                    'name' => 'p',
                    'elements' => $backLinkElements
                );
            }

            $Element['elements'][1]['elements'] [] = array(
                'name' => 'li',
                'attributes' => array('id' => 'fn:'.$definitionId),
                'elements' => array_merge(
                    $textElements
                ),
            );
        }

        return $Element;
    }

    # ~

    protected function parseAttributeData($attributeString)
    {
        $Data = array();

        $attributes = preg_split('/[ ]+/', $attributeString, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($attributes as $attribute) {
            if ($attribute[0] === '#') {
                $Data['id'] = substr($attribute, 1);
            } else { # "."
                $classes [] = substr($attribute, 1);
            }
        }

        if (isset($classes)) {
            $Data['class'] = implode(' ', $classes);
        }

        return $Data;
    }

    # ~

    protected function processTag($elementMarkup) # recursive
    {
        # http://stackoverflow.com/q/1148928/200145
        libxml_use_internal_errors(true);

        $DOMDocument = new \DOMDocument();

        # http://stackoverflow.com/q/11309194/200145
        $elementMarkup = mb_convert_encoding($elementMarkup, 'HTML-ENTITIES', 'UTF-8');

        # http://stackoverflow.com/q/4879946/200145
        $DOMDocument->loadHTML($elementMarkup);
        $DOMDocument->removeChild($DOMDocument->doctype);
        $DOMDocument->replaceChild($DOMDocument->firstChild->firstChild->firstChild, $DOMDocument->firstChild);

        $elementText = '';

        if ($DOMDocument->documentElement->getAttribute('markdown') === '1') {
            foreach ($DOMDocument->documentElement->childNodes as $Node) {
                $elementText .= $DOMDocument->saveHTML($Node);
            }

            $DOMDocument->documentElement->removeAttribute('markdown');

            $elementText = "\n".$this->advanced_markdown_text($elementText)."\n";
        } else {
            foreach ($DOMDocument->documentElement->childNodes as $Node) {
                $nodeMarkup = $DOMDocument->saveHTML($Node);

                if ($Node instanceof DOMElement and !in_array($Node->nodeName, $this->textLevelElements)) {
                    $elementText .= $this->processTag($nodeMarkup);
                } else {
                    $elementText .= $nodeMarkup;
                }
            }
        }

        # because we don't want for markup to get encoded
        $DOMDocument->documentElement->nodeValue = 'placeholder\x1A';

        $markup = $DOMDocument->saveHTML($DOMDocument->documentElement);
        $markup = str_replace('placeholder\x1A', $elementText, $markup);

        return $markup;
    }

    # ~

    protected function sortFootnotes($A, $B) # callback
    {
        return $A['number'] - $B['number'];
    }

    #
    # Fields
    #

}
