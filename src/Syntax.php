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

    private $shortcodeRegex;
    private $singleShortcodeRegex;
    private $argumentsRegex;

    public function __construct($openingTag = null, $closingTag = null, $closingTagMarker = null,
                                $parameterValueSeparator = null, $parameterValueDelimiter = null)
        {
        $this->openingTag = $openingTag ?: '[';
        $this->closingTag = $closingTag ?: ']';
        $this->closingTagMarker = $closingTagMarker ?: '/';
        $this->parameterValueSeparator = $parameterValueSeparator ?: '=';
        $this->parameterValueDelimiter = $parameterValueDelimiter ?: '"';

        $shortcodeRegex = $this->createShortcodeRegexContent();
        $this->shortcodeRegex = '~'.$shortcodeRegex.'~us';
        $this->singleShortcodeRegex = '~^'.$shortcodeRegex.'$~us';
        $this->createArgumentsRegex();
        }

    public function getShortcodeRegex()
        {
        return $this->shortcodeRegex;
        }

    public function getSingleShortcodeRegex()
        {
        return $this->singleShortcodeRegex;
        }

    public function getArgumentsRegex()
        {
        return $this->argumentsRegex;
        }

    private function createArgumentsRegex()
        {
        $equals = $this->quote($this->getParameterValueSeparator());
        $string = $this->quote($this->getParameterValueDelimiter());

        // lookahead test for either space or end of string
        $empty = '(?=\s|$)';
        // equals sign and alphanumeric value
        $simple = '\s*'.$equals.'\s*\w+';
        // equals sign and value without unescaped string delimiters enclosed in them
        $complex = '\s*'.$equals.'\s*'.$string.'([^'.$string.'\\\\]*(?:\\\\.[^'.$string.'\\\\]*)*?)'.$string;

        $this->argumentsRegex = '~(?:\s*(\w+(?:'.$simple.'|'.$complex.'|'.$empty.')))~us';
        }

    private function createShortcodeRegexContent()
        {
        $open = $this->quote($this->getOpeningTag());
        $slash = $this->quote($this->getClosingTagMarker());
        $close = $this->quote($this->getClosingTag());

        // alphanumeric characters and dash
        $name = '\s*([\w-]+)';
        // any characters that are not closing tag marker
        $parameters = '(\s+[^'.$slash.']+?)?';
        // non-greedy match for any characters
        $content = '(.*?)';

        // open tag, name, parameters, maybe some spaces, closing marker, closing tag
        $selfClosed  = $open.$name.$parameters.'\s*'.$slash.'\s*'.$close;
        // open tag, name, parameters, closing tag, maybe some content and closing
        // block with backreference name validation
        $closingTag = $open.'\s*'.$slash.'\s*(\4)\s*'.$close;
        $withContent = $open.$name.$parameters.'\s*'.$close.'(?:'.$content.$closingTag.')?';

        return '((?:'.$selfClosed.'|'.$withContent.'))';
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
