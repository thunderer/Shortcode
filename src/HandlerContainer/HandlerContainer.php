<?php
namespace Thunder\Shortcode\HandlerContainer;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class HandlerContainer implements HandlerContainerInterface
    {
    /** @var callable[] */
    private $handlers = array();

    /** @var callable */
    private $defaultHandler = null;

    public function __construct()
        {
        }

    public function addHandler($name, $handler)
        {
        $this->guardHandler($handler);

        if(!$name || $this->hasHandler($name))
            {
            $msg = 'Invalid name or duplicate shortcode handler for %s!';
            throw new \RuntimeException(sprintf($msg, $name));
            }

        $this->handlers[$name] = $handler;

        return $this;
        }

    public function addAlias($alias, $name)
        {
        $handler = $this->getHandler($name);

        $this->addHandler($alias, function(ShortcodeInterface $shortcode) use($handler) {
            return call_user_func_array($handler, array($shortcode));
            });

        return $this;
        }

    public function setDefault($handler)
        {
        $this->guardHandler($handler);

        $this->defaultHandler = $handler;
        }

    public function getHandler($name)
        {
        return $this->hasHandler($name)
            ? $this->handlers[$name]
            : ($this->defaultHandler ? $this->defaultHandler : null);
        }

    public function hasHandler($name)
        {
        return array_key_exists($name, $this->handlers);
        }

    private function guardHandler($handler)
        {
        if(!is_callable($handler))
            {
            $msg = 'Shortcode handler must be callable!';
            throw new \RuntimeException(sprintf($msg));
            }
        }
    }
