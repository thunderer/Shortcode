<?php
namespace Thunder\Shortcode;

use Thunder\Shortcode\Processor\ProcessorInterface as ProcessorInterfaceBase;

/**
 * This implementation is left only to not break IDE autocompletion, this class
 * is deprecated, it was moved to the new location as specified in docblock.
 * This file will be removed in version 1.0!
 *
 * @deprecated use Thunder\Shortcode\Extractor\RegexExtractor
 * @codeCoverageIgnore
 */
interface ProcessorInterface extends ProcessorInterfaceBase
    {
    }
