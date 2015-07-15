<?php
namespace Thunder\Shortcode\Tests\Fake;

use Thunder\Shortcode\Shortcode;

class ReverseShortcode
    {
    public function __invoke(Shortcode $s)
        {
        return strrev($s->getContent());
        }
    }
