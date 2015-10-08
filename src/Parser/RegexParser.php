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
final class RegexParser implements ParserInterface
{
    /** @var SyntaxInterface */
    private $syntax;
    private $shortcodeRegex;
    private $singleShortcodeRegex;
    private $argumentsRegex;

    public function __construct(SyntaxInterface $syntax = null)
    {
        $this->syntax = $syntax ?: new Syntax();
        $this->shortcodeRegex = RegexBuilderUtility::buildShortcodeRegex($this->syntax);
        $this->singleShortcodeRegex = RegexBuilderUtility::buildSingleShortcodeRegex($this->syntax);
        $this->argumentsRegex = RegexBuilderUtility::buildArgumentsRegex($this->syntax);
    }

    /**
     * @param string $text
     *
     * @return ParsedShortcode[]
     */
    public function parse($text)
    {
        preg_match_all($this->shortcodeRegex, $text, $matches, PREG_OFFSET_CAPTURE);

        // loop instead of array_map to pass the arguments explicitly
        $shortcodes = array();
        foreach($matches[0] as $match) {
            $shortcodes[] = $this->parseSingle($match[0], $match[1]);
        }

        return $shortcodes;
    }

    private function parseSingle($text, $offset)
    {
        preg_match($this->singleShortcodeRegex, $text, $matches, PREG_OFFSET_CAPTURE);

        $name = $matches['name'][0];
        $parameters = isset($matches['parameters'][0]) ? $this->parseParameters($matches['parameters'][0]) : array();
        $bbCode = isset($matches['bbCode'][0]) && $matches['bbCode'][1] !== -1
            ? $this->extractValue($matches['bbCode'][0])
            : null;
        $content = isset($matches['content'][0]) && $matches['content'][1] !== -1 ? $matches['content'][0] : null;
        $offsets = array(
            'name' => $matches['name'][1],
            'parameters' => isset($matches['parameters'][1]) ? $matches['parameters'][1] : null,
            'content' => isset($matches['content'][1]) ? $matches['content'][1] : null,
            'marker' => isset($matches['marker'][1]) ? $matches['marker'][1] : null,
        );

        return new ParsedShortcode(new Shortcode($name, $parameters, $content, $bbCode), $text, $offset, $offsets);
    }

    private function parseParameters($text)
    {
        preg_match_all($this->argumentsRegex, $text, $argsMatches);

        // loop because PHP 5.3 can't handle $this properly and I want separate methods
        $return = array();
        foreach ($argsMatches[1] as $item) {
            $parts = explode($this->syntax->getParameterValueSeparator(), $item, 2);
            $return[trim($parts[0])] = $this->parseValue(isset($parts[1]) ? $parts[1] : null);
        }

        return $return;
    }

    private function parseValue($value)
    {
        return null === $value ? null : $this->extractValue(trim($value));
    }

    private function extractValue($value)
    {
        $length = strlen($this->syntax->getParameterValueDelimiter());

        return $this->isDelimitedValue($value) ? substr($value, $length, -1 * $length) : $value;
    }

    private function isDelimitedValue($value)
    {
        return preg_match('/^'.$this->syntax->getParameterValueDelimiter().'/us', $value)
            && preg_match('/'.$this->syntax->getParameterValueDelimiter().'$/us', $value);
    }
}
