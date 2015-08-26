<?php
namespace Thunder\Shortcode\HandlerContainer;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainer implements HandlerContainerInterface
{
    /** @var callable[] */
    protected $handlers = array();

    /** @var callable */
    private $default;

    public function add($name, $handler)
    {
        $this->guardHandler($handler);

        if (!$name || $this->has($name)) {
            $msg = 'Invalid name or duplicate shortcode handler for %s!';
            throw new \RuntimeException(sprintf($msg, $name));
        }

        $this->handlers[$name] = $handler;

        return $this;
    }

    public function addAlias($alias, $name)
    {
        $handler = $this->get($name);

        if (!$handler) {
            $msg = 'Failed to add an alias %s to non existent handler %s!';
            throw new \InvalidArgumentException(sprintf($msg, $alias, $name));
        }

        $this->add($alias, function (ShortcodeInterface $shortcode) use ($handler) {
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

    public function get($name)
    {
        return $this->has($name)
            ? $this->handlers[$name]
            : ($this->default ?: null);
    }

    private function has($name)
    {
        return array_key_exists($name, $this->handlers);
    }

    private function guardHandler($handler)
    {
        if (!is_callable($handler)) {
            $msg = 'Shortcode handler must be callable!';
            throw new \RuntimeException(sprintf($msg));
        }
    }
}
