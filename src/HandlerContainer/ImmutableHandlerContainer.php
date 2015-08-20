<?php
namespace Thunder\Shortcode\HandlerContainer;

final class ImmutableHandlerContainer implements HandlerContainerInterface
    {
    private $handlers;

    public function __construct(HandlerContainer $handlers)
        {
        $this->handlers = $handlers;
        }

    public function get($name)
        {
        return $this->handlers->get($name);
        }
    }
