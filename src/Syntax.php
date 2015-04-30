<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Syntax
    {
    private $openingTag;
    private $closingTag;
    private $closingTagMarker;
    private $parameterValueSeparator;
    private $parameterValueDelimiter;
    private static $regexCache = array();

    public function __construct($openingTag = null, $closingTag = null, $closingTagMarker = null,
                                $parameterValueSeparator = null, $parameterValueDelimiter = null)
        {
        $this->openingTag = $openingTag ?: '[';
        $this->closingTag = $closingTag ?: ']';
        $this->closingTagMarker = $closingTagMarker ?: '/';
        $this->parameterValueSeparator = $parameterValueSeparator ?: '=';
        $this->parameterValueDelimiter = $parameterValueDelimiter ?: '"';
        }

    public function getShortcodeRegex()
        {
        return '~'.$this->createShortcodeRegex().'~us';
        }

    public function getSingleShortcodeRegex()
        {
        return '~^'.$this->createShortcodeRegex().'$~us';
        }

    public function getArgumentsRegex()
        {
        $equals = $this->quote($this->getParameterValueSeparator());
        $quote = $this->quote($this->getParameterValueDelimiter());
        $cacheKey = "arguments-$equals-$quote";

        if (!isset(self::$regexCache[$cacheKey])) {

            $regex =
                    '(?:' .
                        '\s+' .
                        '(' .
                            '\w+' .
                            '(?:' .
                                '(?=\s|$)' .    // lookahead test for either space or end of string
                                '|' .
                                $equals .       // equals sign and alphanumeric value
                                '\w+' .
                                '|' .
                                $equals .       // equals sign and value without unescaped string delimiters enclosed in them
                                $quote .
                                '(' .
                                    '[^' .
                                        $quote .
                                        '\\\\' .
                                    ']*' .
                                    '(?:' .
                                        '\\\\.' .
                                        '[^' .
                                            $quote .
                                            '\\\\' .
                                        ']*' .
                                    ')*?' .
                                ')' .
                                $quote .
                            ')' .
                        ')' .
                    ')';

            self::$regexCache[$cacheKey] = "~$regex~us";
        }

        return self::$regexCache[$cacheKey];
        }

    private function createShortcodeRegex()
        {
        $openingTag = $this->quote($this->getOpeningTag());
        $closingTagMarker = $this->quote($this->getClosingTagMarker());
        $closingTag = $this->quote($this->getClosingTag());
        $cacheKey = "shortcode-$openingTag-$closingTagMarker-$closingTag";

        if (!isset(self::$regexCache[$cacheKey])) {

            // alphanumeric characters and dash
            $name = '([\w-]+)';
            // any characters that are not closing tag marker
            $parameters = '(\s+[^'.$closingTagMarker.']+?)?';
            // non-greedy match for any characters
            $content = '(.*?)';

            $optionalWhitespace = '\s*';

            $openingTag = $openingTag . $optionalWhitespace;
            $closingTag = $optionalWhitespace . $closingTag;

            self::$regexCache[$cacheKey] =

                '(' .
                    '(?:' .
                        $openingTag .
                        $name .
                        $parameters .
                        $optionalWhitespace .
                        $closingTagMarker .
                        $closingTag .
                        '|' .
                        $openingTag .
                        $name .
                        $parameters .
                        $closingTag .
                        '(?:' .
                            $content .
                            $openingTag .
                            $closingTagMarker .
                            '(\4)' .
                            $closingTag .
                        ')?' .
                    ')' .
                ')';
        }

        return self::$regexCache[$cacheKey];
        }

    private function quote($text)
        {
        return preg_replace('/(.)/us', '\\\\$0', $text);
        }

    /* --- GETTERS --- */

    public function getOpeningTag()
        {
        return $this->openingTag;
        }

    public function getClosingTag()
        {
        return $this->closingTag;
        }

    public function getClosingTagMarker()
        {
        return $this->closingTagMarker;
        }

    public function getParameterValueSeparator()
        {
        return $this->parameterValueSeparator;
        }

    public function getParameterValueDelimiter()
        {
        return $this->parameterValueDelimiter;
        }
    }
