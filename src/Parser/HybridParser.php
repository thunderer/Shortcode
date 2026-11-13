<?php
namespace Thunder\Shortcode\Parser;

// @mago-expect analysis:unused-statement
class_exists('Thunder\Shortcode\Parser\StackParser');

/** @psalm-suppress TypeDoesNotContainType */
// @mago-expect analysis:impossible-condition
if(false) {
    /**
     * @deprecated Use StackParser instead, this alias will be removed in 2.0.
     */
    final class HybridParser implements ParserInterface
    {
        /** @param \Thunder\Shortcode\Syntax\SyntaxInterface|null $syntax */
        public function __construct($syntax = null)
        {
        }

        /**
         * @param string $text
         *
         * @return \Thunder\Shortcode\Shortcode\ParsedShortcode[]
         */
        public function parse($text)
        {
            return array();
        }
    }
}
