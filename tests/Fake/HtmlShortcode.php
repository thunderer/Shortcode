<?php
namespace Thunder\Shortcode\Tests\Fake;

use Thunder\Shortcode\HandlerInterface;
use Thunder\Shortcode\Shortcode;

class HtmlShortcode implements HandlerInterface
{
    public function isValid(Shortcode $s)
    {
        return $s->hasParameters();
    }

    public function handle(Shortcode $s)
    {
        $tag = $s->getParameterAt(0);

        return '<'.$tag.'>'.$s->getContent().'</'.$tag.'>';
    }
}
