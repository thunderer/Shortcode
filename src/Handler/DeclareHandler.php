<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class DeclareHandler
{
    /** @var HandlerContainer */
    private $handlers;

    public function __construct(HandlerContainer $container)
    {
        $this->handlers = $container;
    }

    public function __invoke(ShortcodeInterface $shortcode)
    {
        $args = $shortcode->getParameters();
        if(empty($args)) {
            return;
        }
        $keys = array_keys($args);
        $name = array_shift($keys);
        $content = $shortcode->getContent();

        $this->handlers->add($name, function(ShortcodeInterface $shortcode) use($content) {
            $args = $shortcode->getParameters();
            $keys = array_map(function($key) { return '%'.$key.'%'; }, array_keys($args));
            $values = array_values($args);

            return str_replace($keys, $values, $content);
        });
    }
}
