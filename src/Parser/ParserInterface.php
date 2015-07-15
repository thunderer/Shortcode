<?php
namespace Thunder\Shortcode\Parser;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

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
     * @return ShortcodeInterface
     */
    public function parse($text);
    }
