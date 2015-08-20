<?php
namespace Thunder\Shortcode\HandlerContainer;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

final class HandlerContainer extends AbstractHandlerContainer
    {
    public function add($name, $handler)
        {
        $this->guardHandler($handler);

        if(!$name || $this->has($name))
            {
            $msg = 'Invalid name or duplicate shortcode handler for %s!';
            throw new \RuntimeException(sprintf($msg, $name));
            }

        $this->handlers[$name] = $handler;

        return $this;
        }

    public function addAlias($alias, $name)
        {
        $handler = $this->get($name);

        $this->add($alias, function(ShortcodeInterface $shortcode) use($handler) {
            return call_user_func_array($handler, array($shortcode));
            });

        return $this;
        }

    public function setDefault($handler)
        {
        $this->guardHandler($handler);

        $this->default = $handler;

        return $this;
        }
    }
