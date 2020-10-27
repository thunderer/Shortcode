<?php
namespace Thunder\Shortcode\HandlerContainer;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ImmutableHandlerContainer implements HandlerContainerInterface
{
    /** @var HandlerContainer */
    private $handlers;

    public function __construct(HandlerContainer $handlers)
    {
        $this->handlers = clone $handlers;
    }

    /**
     * @param string $name
     *
     * @return callable|null
     * @psalm-return (callable(ShortcodeInterface):string)|null
     */
    public function get($name)
    {
        return $this->handlers->get($name);
    }
}
