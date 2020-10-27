<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
abstract class AbstractShortcode
{
    /** @var string */
    protected $name;
    /** @psalm-var array<string,string|null> */
    protected $parameters = array();
    /** @var string|null */
    protected $content;
    /** @var string|null */
    protected $bbCode;

    /** @return bool */
    public function hasContent()
    {
        return $this->content !== null;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    /** @psalm-return array<string,string|null> */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /** @return bool */
    public function hasParameters()
    {
        return (bool)$this->parameters;
    }

    /**
     * @param string $name
     * @param string|null $default
     *
     * @psalm-return string|null
     */
    public function getParameter($name, $default = null)
    {
        return $this->hasParameter($name) ? $this->parameters[$name] : $default;
    }

    /**
     * @param int $index
     *
     * @return string|null
     */
    public function getParameterAt($index)
    {
        $keys = array_keys($this->parameters);

        return array_key_exists($index, $keys) ? $keys[$index] : null;
    }

    /** @return string|null */
    public function getContent()
    {
        return $this->content;
    }

    /** @return string|null */
    public function getBbCode()
    {
        return $this->bbCode;
    }
}
