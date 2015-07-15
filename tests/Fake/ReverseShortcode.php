<?php
namespace Thunder\Shortcode\Tests\Fake;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class ReverseShortcode
    {
    public function __invoke(ShortcodeInterface $s)
        {
        return strrev($s->getContent());
        }
    }
