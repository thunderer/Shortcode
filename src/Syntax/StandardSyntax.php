<?php
namespace Thunder\Shortcode\Syntax;

use Thunder\Shortcode\SyntaxInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class StandardSyntax implements SyntaxInterface
    {
    public function getOpeningTag()
        {
        return '[';
        }

    public function getClosingTag()
        {
        return ']';
        }

    public function getClosingTagMarker()
        {
        return '/';
        }

    public function getParameterValueSeparator()
        {
        return '=';
        }

    public function getParameterValueDelimiter()
        {
        return '"';
        }
    }
