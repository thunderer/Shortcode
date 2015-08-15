<?php
namespace Thunder\Shortcode\HandlerContainer;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class HandlerContainer implements HandlerContainerInterface
    {
    /** @var callable[] */
    private $handlers = array();

    public function __construct()
        {
        }

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

    public function get($name)
        {
        if(!$this->has($name))
            {
            throw new \RuntimeException(sprintf('Handler for name %s does not exist!', $name));
            }

        return $this->handlers[$name];
        }

    public function has($name)
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
