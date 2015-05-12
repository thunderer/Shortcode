<?php
namespace Thunder\Shortcode;

class_alias('Thunder\\Shortcode\\Extractor\\RegexExtractor', 'Thunder\\Shortcode\\Extractor', true);
return;

/**
 * This implementation is left only to not break IDE autocompletion, this class
 * is deprecated, it was moved to the new location as specified in docblock.
 * This file will be removed in version 1.0!
 *
 * @deprecated use Thunder\Shortcode\Extractor\RegexExtractor
 * @codeCoverageIgnore
 */
class Extractor implements ExtractorInterface
    {
    public function extract($text)
        {
        return array();
        }
    }
