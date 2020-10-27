<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class CommonSyntax implements SyntaxInterface
{
    /** @return string */
    public function getOpeningTag()
    {
        return '[';
    }

    /** @return string */
    public function getClosingTag()
    {
        return ']';
    }

    /** @return string */
    public function getClosingTagMarker()
    {
        return '/';
    }

    /** @return string */
    public function getParameterValueSeparator()
    {
        return '=';
    }

    /** @return string */
    public function getParameterValueDelimiter()
    {
        return '"';
    }
}
