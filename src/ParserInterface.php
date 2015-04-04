<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface ParserInterface
    {
    /**
     * Parse single shortcode match into object
     *
     * @param string $text
     *
     * @return Shortcode
     */
    public function parse($text);
    }
