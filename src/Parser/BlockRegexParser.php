<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Syntax\Syntax;
use Thunder\Shortcode\Syntax\SyntaxInterface;
use Thunder\Shortcode\Utility\RegexBuilderUtility;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class BlockRegexParser implements ParserInterface
{
    /** @var SyntaxInterface */
    private $syntax;
    private $regex;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $this->syntax = $syntax ?: new Syntax();
        $this->regex = RegexBuilderUtility::buildOpeningClosingRegex($this->syntax);
    }

    /**
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    public function parse($text)
    {
        preg_match_all($this->regex, $text, $matches, PREG_OFFSET_CAPTURE);

        $names = array();
        $shortcodes = array();
        foreach($matches[0] as $match) {
            $fragment = $match[0];
            $offset = $match[1];
            preg_match($this->regex, $fragment, $sub, PREG_OFFSET_CAPTURE);
            if(isset($sub['name']) && $sub['name'][1] !== -1) {
                array_push($names, $sub['name'][0]);
            } elseif(isset($sub['closing']) && $sub['closing'] !== -1) {
                if(in_array($sub['closing'][0], $names, true)) {
                    while(end($names) !== $sub['closing'][0]) {
                        array_pop($names);
                    }
                    array_pop($names);
                }
                if(count($names) === 1 && $sub['closing'][0] === $names[0]) {
                    array_pop($names);
                    $shortcode = new Shortcode($sub['closing'][0], array(), null);
                    $shortcodes[] = new ParsedShortcode($shortcode, $fragment, $offset);
                }
            }
        }

        return $shortcodes;
    }
}
