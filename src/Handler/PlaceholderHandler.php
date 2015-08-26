<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class PlaceholderHandler
{
    public function __invoke(ShortcodeInterface $shortcode)
    {
        $args = $shortcode->getParameters();
        $keys = array_map(function($key) { return '%'.$key.'%'; }, array_keys($args));
        $values = array_values($args);

        return str_replace($keys, $values, $shortcode->getContent());
    }
}
