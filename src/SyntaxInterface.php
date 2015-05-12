<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface SyntaxInterface
    {
    public function getOpeningTag();

    public function getClosingTag();

    public function getClosingTagMarker();

    public function getParameterValueSeparator();

    public function getParameterValueDelimiter();
    }
