<?php
namespace Thunder\Shortcode\Utility;

use Thunder\Shortcode\Syntax;

class RegexBuilderUtility
    {
    public static function buildShortcodeRegex(Syntax $syntax)
        {
        return '~'.static::createShortcodeRegexContent($syntax).'~us';
        }

    public static function buildSingleShortcodeRegex(Syntax $syntax)
        {
        return '~^'.static::createShortcodeRegexContent($syntax).'$~us';
        }

    public static function buildArgumentsRegex(Syntax $syntax)
        {
        $equals = static::quote($syntax->getParameterValueSeparator());
        $string = static::quote($syntax->getParameterValueDelimiter());

        $ws = '\s*';
        // lookahead test for either space or end of string
        $empty = '(?=\s|$)';
        // equals sign and alphanumeric value
        $simple = $ws.$equals.$ws.'\w+';
        // equals sign and value without unescaped string delimiters enclosed in them
        $complex = $ws.$equals.$ws.$string.'([^'.$string.'\\\\]*(?:\\\\.[^'.$string.'\\\\]*)*?)'.$string;

        return '~(?:\s*(\w+(?:'.$simple.'|'.$complex.'|'.$empty.')))~us';
        }

    private static function createShortcodeRegexContent(Syntax $syntax)
        {
        $open = static::quote($syntax->getOpeningTag());
        $slash = static::quote($syntax->getClosingTagMarker());
        $close = static::quote($syntax->getClosingTag());

        $ws = '\s*';
        // alphanumeric characters and dash
        $name = $ws.'([\w-]+)';
        // any characters that are not closing tag marker
        $parameters = '(\s+[^'.$slash.']+?)?';
        // non-greedy match for any characters
        $content = '(.*?)';

        // open tag, name, parameters, maybe some spaces, closing marker, closing tag
        $selfClosed  = $open.$name.$parameters.$ws.$slash.$ws.$close;
        // open tag, name, parameters, closing tag, maybe some content and closing
        // block with backreference name validation
        $closingTag = $open.$ws.$slash.$ws.'(\4)'.$ws.$close;
        $withContent = $open.$name.$parameters.$ws.$close.'(?:'.$content.$closingTag.')?';

        return '((?:'.$selfClosed.'|'.$withContent.'))';
        }

    private static function quote($text)
        {
        return preg_replace('/(.)/us', '\\\\$0', $text);
        }
    }
