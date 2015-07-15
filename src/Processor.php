<?php
namespace Thunder\Shortcode;

class_alias('Thunder\\Shortcode\\Processor\\Processor', 'Thunder\\Shortcode\\Processor', true);
return;

/**
 * This implementation is left only to not break IDE autocompletion, this class
 * is deprecated, it was moved to the new location as specified in docblock.
 * This file will be removed in version 1.0!
 *
 * @deprecated use Thunder\Shortcode\Processor\Processor
 * @codeCoverageIgnore
 */
class Processor implements ProcessorInterface
    {
    public function process($text)
        {
        return '';
        }

    public function addHandler($name, $handler)
        {
        return $this;
        }

    public function addHandlerAlias($alias, $name)
        {
        return $this;
        }

    public function setRecursion($status)
        {
        return $this;
        }

    public function setMaxIterations($status)
        {
        return $this;
        }

    public function setDefaultHandler($status)
        {
        return $this;
        }

    public function setAutoProcessContent($status)
        {
        return $this;
        }

    public function setRecursionDepth($status)
        {
        return $this;
        }
    }
