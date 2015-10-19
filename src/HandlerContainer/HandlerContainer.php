<?php
namespace Thunder\Shortcode\HandlerContainer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainer implements HandlerContainerInterface
{
    /** @var callable[] */
    private $handlers = array();

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
            throw new \RuntimeException(sprintf($msg, $alias, $name));
        }

        return $this->add($alias, $handler);
    }

    public function remove($name)
    {
        $handler = $this->get($name);

        if (!$handler) {
            $msg = 'Failed to remove non existent handler %s!';
            throw new \RuntimeException(sprintf($msg, $name));
        }

        unset($this->handlers[$name]);
    }

    public function setDefault($handler)
    {
        $this->guardHandler($handler);

        $this->default = $handler;

        return $this;
    }

    public function getNames()
    {
        return array_keys($this->handlers);
    }

    public function get($name)
    {
        return $this->has($name) ? $this->handlers[$name] : ($this->default ?: null);
    }

    public function has($name)
    {
        return array_key_exists($name, $this->handlers);
    }

    private function guardHandler($handler)
    {
        if (!is_callable($handler)) {
            throw new \RuntimeException('Shortcode handler must be callable!');
        }
    }
}
