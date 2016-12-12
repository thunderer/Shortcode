<?php
namespace Thunder\Shortcode\HandlerContainer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainer implements HandlerContainerInterface
{
    /** @var callable[] */
    private $handlers = array();

    /** @var callable[] */
    private $patterns = array();

    /** @var callable */
    private $default;

    public function add($name, $handler)
    {
        $this->guardHandler($handler);

        if (empty($name) || $this->has($name)) {
            $msg = 'Invalid name or duplicate shortcode handler for %s!';
            throw new \RuntimeException(sprintf($msg, $name));
        }

        $this->handlers[$name] = $handler;

        return $this;
    }

    public function addPattern($pattern, $handler)
    {
        $this->guardHandler($handler);

        if (empty($pattern) || $this->hasPattern($pattern)) {
            $msg = 'Invalid name or duplicate shortcode handler pattern for `%s`!';
            throw new \RuntimeException(sprintf($msg, $pattern));
        }

        $this->patterns[$pattern] = $handler;
    }

    public function addAlias($alias, $name)
    {
        if (false === $this->has($name)) {
            $msg = 'Failed to add an alias %s to non existent handler %s!';
            throw new \RuntimeException(sprintf($msg, $alias, $name));
        }

        return $this->add($alias, $this->get($name));
    }

    public function remove($name)
    {
        if (false === $this->has($name)) {
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
        if($this->has($name)) {
            return $this->handlers[$name];
        }

        foreach($this->patterns as $pattern => $handler) {
            if(preg_match($pattern, $name)) {
                return $handler;
            }
        }

        return $this->default ?: null;
    }

    public function has($name)
    {
        return array_key_exists($name, $this->handlers);
    }

    private function hasPattern($pattern)
    {
        return array_key_exists($pattern, $this->patterns);
    }

    private function guardHandler($handler)
    {
        if (!is_callable($handler)) {
            throw new \RuntimeException('Shortcode handler must be callable!');
        }
    }
}
