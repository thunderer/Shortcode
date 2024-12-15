<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface SyntaxInterface
{
    /** @return non-empty-string */
    public function getOpeningTag();

    /** @return non-empty-string */
    public function getClosingTag();

    /** @return non-empty-string */
    public function getClosingTagMarker();

    /** @return non-empty-string */
    public function getParameterValueSeparator();

    /** @return non-empty-string */
    public function getParameterValueDelimiter();
}
