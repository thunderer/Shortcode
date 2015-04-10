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
        $open = $this->quote($this->getOpeningTag());
        $slash = $this->quote($this->getClosingTagMarker());
        $close = $this->quote($this->getClosingTag());

        return '~('.$open.'([\w-]+)(\s+.+?)?'.$close.'(?:(.*?)'.$open.$slash.'(\2)'.$close.')?)~us';
        }

    public function getSingleShortcodeRegex()
        {
        $open = $this->quote($this->getOpeningTag());
        $slash = $this->quote($this->getClosingTagMarker());
        $close = $this->quote($this->getClosingTag());

        return '~^('.$open.'([\w-]+)(\s+.+?)?'.$close.'(?:(.*?)'.$open.$slash.'(\2)'.$close.')?)$~us';
        }

    public function getArgumentsRegex()
        {
        $equals = $this->quote($this->getParameterValueSeparator());
        $string = $this->quote($this->getParameterValueDelimiter());

        return '~(?:\s+(\w+(?:(?=\s|$)|'.$equals.'\w+|'.$equals.$string.'.+'.$string.')))~us';
        }

    private function quote($text)
        {
        return preg_replace('/(.)/us', '\\\\$0', $text);
        }

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
