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

    public function addHandler()
        {
        return $this;
        }

    public function addHandlerAlias()
        {
        return $this;
        }

    public function setRecursion()
        {
        return $this;
        }

    public function setMaxIterations()
        {
        return $this;
        }

    public function setDefaultHandler()
        {
        return $this;
        }

    public function setAutoProcessContent()
        {
        return $this;
        }

    public function setRecursionDepth()
        {
        return $this;
        }
    }
