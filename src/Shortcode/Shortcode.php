<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Shortcode extends AbstractShortcode implements ShortcodeInterface
{
    public function __construct($name, array $parameters, $content, $bbCode = null)
    {
        $this->name = $name;
        $this->parameters = $parameters;
        $this->content = $content;
        $this->bbCode = $bbCode;
    }

    public function withContent($content)
    {
        return new self($this->name, $this->parameters, $content, $this->bbCode);
    }
}
