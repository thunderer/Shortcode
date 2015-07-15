<?php
namespace Thunder\Shortcode\Tests\Fake;

use Thunder\Shortcode\Handler\HandlerInterface;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class HtmlShortcode implements HandlerInterface
    {
    public function isValid(ShortcodeInterface $s)
        {
        return $s->hasParameters();
        }

    public function handle(ShortcodeInterface $s)
        {
        $tag = $s->getParameterAt(0);

        return '<'.$tag.'>'.$s->getContent().'</'.$tag.'>';
        }
    }
