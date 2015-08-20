<?php
namespace Thunder\Shortcode\HandlerContainer;

final class ImmutableHandlerContainer extends AbstractHandlerContainer
    {
    public function __construct(array $handlers, array $aliases, $defaultHandler = null)
        {
        $this->handlers = new HandlerContainer();

        foreach($handlers as $name => $handler)
            {
            $this->handlers->add($name, $handler);
            }

        foreach($aliases as $alias => $name)
            {
            $this->handlers->addAlias($alias, $name);
            }

        !$defaultHandler ?: $this->handlers->setDefault($defaultHandler);
        }

    public function get($name)
        {
        return $this->handlers->get($name);
        }
    }
