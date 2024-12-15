<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class CommonSyntax implements SyntaxInterface
{
    /** @return non-empty-string */
    public function getOpeningTag()
    {
        return '[';
    }

    /** @return non-empty-string */
    public function getClosingTag()
    {
        return ']';
    }

    /** @return non-empty-string */
    public function getClosingTagMarker()
    {
        return '/';
    }

    /** @return non-empty-string */
    public function getParameterValueSeparator()
    {
        return '=';
    }

    /** @return non-empty-string */
    public function getParameterValueDelimiter()
    {
        return '"';
    }
}
