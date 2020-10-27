<?php
namespace Thunder\Shortcode\HandlerContainer;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainer implements HandlerContainerInterface
{
    /** @psalm-var array<string,callable(ShortcodeInterface):string> */
    private $handlers = array();
    /** @psalm-var (callable(ShortcodeInterface):string)|null */
    private $default = null;

    /**
     * @param string $name
     * @param callable $handler
     * @psalm-param callable(ShortcodeInterface):string $handler
     *
     * @return $this
     */
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

    /**
     * @param string $alias
     * @param string $name
     *
     * @return $this
     */
    public function addAlias($alias, $name)
    {
        if (false === $this->has($name)) {
            $msg = 'Failed to add an alias %s to non existent handler %s!';
            throw new \RuntimeException(sprintf($msg, $alias, $name));
        }

        /** @psalm-suppress PossiblyNullArgument */
        return $this->add($alias, $this->get($name));
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function remove($name)
    {
        if (false === $this->has($name)) {
            $msg = 'Failed to remove non existent handler %s!';
            throw new \RuntimeException(sprintf($msg, $name));
        }

        unset($this->handlers[$name]);
    }

    /**
     * @param callable $handler
     * @psalm-param callable(ShortcodeInterface):string $handler
     *
     * @return $this
     */
    public function setDefault($handler)
    {
        $this->guardHandler($handler);

        $this->default = $handler;

        return $this;
    }

    /** @return string[] */
    public function getNames()
    {
        return array_keys($this->handlers);
    }

    /**
     * @param string $name
     *
     * @return callable|null
     * @psalm-return (callable(ShortcodeInterface):string)|null
     */
    public function get($name)
    {
        return $this->has($name) ? $this->handlers[$name] : $this->default;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->handlers);
    }

    /**
     * @param callable $handler
     *
     * @return void
     */
    private function guardHandler($handler)
    {
        if (!is_callable($handler)) {
            throw new \RuntimeException('Shortcode handler must be callable!');
        }
    }
}
