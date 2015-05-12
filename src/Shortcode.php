<?php
namespace Thunder\Shortcode;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class_alias('Thunder\\Shortcode\\Shortcode\\Shortcode', 'Thunder\\Shortcode\\Shortcode', true);
return;

/**
 * This implementation is left only to not break IDE autocompletion, this class
 * is deprecated, it was moved to the new location as specified in docblock.
 * This file will be removed in version 1.0!
 *
 * @deprecated use Thunder\Shortcode\Shortcode\Shortcode
 * @codeCoverageIgnore
 */
class Shortcode implements ShortcodeInterface
    {
    public function withContent($content)
        {
        return $this;
        }

    public function hasContent()
        {
        return true;
        }

    public function getName()
        {
        return '';
        }

    public function getParameters()
        {
        return array();
        }

    public function hasParameter($name)
        {
        return true;
        }

    public function hasParameters()
        {
        return true;
        }

    public function getParameter($name, $default = null)
        {
        return null;
        }

    public function getParameterAt($index)
        {
        return null;
        }

    public function getContent()
        {
        return '';
        }
    }
