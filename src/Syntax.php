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
        $string = $this->quote($this->getParameterValueDelimiter());

        // lookahead test for either space or end of string
        $empty = '(?=\s|$)';
        // equals sign and alphanumeric value
        $simple = $equals.'\w+';
        // equals sign and value without unescaped string delimiters enclosed in them
        $complex = $equals.$string.'([^'.$string.'\\\\]*(?:\\\\.[^'.$string.'\\\\]*)*?)'.$string;

        return '~(?:\s+(\w+(?:'.$empty.'|'.$simple.'|'.$complex.')))~us';
        }

    private function createShortcodeRegex()
        {
        $open = $this->quote($this->getOpeningTag());
        $slash = $this->quote($this->getClosingTagMarker());
        $close = $this->quote($this->getClosingTag());

        // alphanumeric characters and dash
        $name = '([\w-]+)';
        // any characters that are not closing tag marker
        $parameters = '(\s+[^'.$slash.']+?)?';
        // non-greedy match for any characters
        $content = '(.*?)';

        // open tag, name, parameters, maybe some spaces, closing marker, closing tag
        $selfClosed  = $open.$name.$parameters.'\s*'.$slash.$close;
        // open tag, name, parameters, closing tag, maybe some content and closing
        // block with backreference name validation
        $withContent = $open.$name.$parameters.$close.'(?:'.$content.$open.$slash.'(\4)'.$close.')?';

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
