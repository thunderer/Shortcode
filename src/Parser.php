<?php
namespace Thunder\Shortcode;

class_alias('Thunder\\Shortcode\\Parser\\RegexParser', 'Thunder\\Shortcode\\Parser', true);
return;

/**
 * This implementation is left only to not break IDE autocompletion, this class
 * is deprecated, it was moved to the new location as specified in docblock.
 * This file will be removed in version 1.0!
 *
 * @deprecated use Thunder\Shortcode\Parser\RegexParser
 * @codeCoverageIgnore
 */
class Parser implements ParserInterface
    {
    public function parse($text)
        {
        return new Shortcode('', array(), '');
        }
    }
