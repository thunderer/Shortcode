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

        return '~(?:\s+(\w+(?:(?=\s|$)|'.$equals.'\w+|'.$equals.$string.'([^'.$string.'\\\\]*(?:\\\\.[^'.$string.'\\\\]*)*?)'.$string.')))~us';
        }

    private function createShortcodeRegex()
        {
        $open = $this->quote($this->getOpeningTag());
        $slash = $this->quote($this->getClosingTagMarker());
        $close = $this->quote($this->getClosingTag());

        $selfClosed  = $open.'([\w-]+)(\s+[^'.$slash.']+?)?\s*'.$slash.$close;
        $withContent = $open.'([\w-]+)(\s+[^'.$slash.']+?)?'.$close.'(?:(.*?)'.$open.$slash.'(\4)'.$close.')?';

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
