<?php
namespace Thunder\Shortcode\HandlerContainer;

abstract class AbstractHandlerContainer implements HandlerContainerInterface
    {
    /** @var callable[] */
    protected $handlers = array();

    /** @var callable */
    protected $default;

    public function get($name)
        {
        return $this->has($name)
            ? $this->handlers[$name]
            : ($this->default ?: null);
        }

    protected function has($name)
        {
        return array_key_exists($name, $this->handlers);
        }

    protected function guardHandler($handler)
        {
        if(!is_callable($handler))
            {
            $msg = 'Shortcode handler must be callable!';
            throw new \RuntimeException(sprintf($msg));
            }
        }
    }
