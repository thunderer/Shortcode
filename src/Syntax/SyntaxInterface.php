<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface SyntaxInterface
{
    /** @return string */
    public function getOpeningTag();

    /** @return string */
    public function getClosingTag();

    /** @return string */
    public function getClosingTagMarker();

    /** @return string */
    public function getParameterValueSeparator();

    /** @return string */
    public function getParameterValueDelimiter();
}
