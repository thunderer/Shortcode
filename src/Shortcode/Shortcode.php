<?php
namespace Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Shortcode extends AbstractShortcode implements ShortcodeInterface
{
    /**
     * @param string $name
     * @param array $parameters
     * @psalm-param array<string,string|null> $parameters
     * @param string|null $content
     * @param string|null $bbCode
     */
    public function __construct($name, array $parameters, $content, $bbCode = null)
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType, DocblockTypeContradiction */
        if(false === is_string($name) || '' === $name) {
            throw new \InvalidArgumentException('Shortcode name must be a non-empty string!');
        }

        foreach($parameters as $value) {
            /** @psalm-suppress DocblockTypeContradiction, RedundantConditionGivenDocblockType */
            if(false === is_string($value) && null !== $value) {
                throw new \InvalidArgumentException('Parameter values must be either string or empty (null)!');
            }
        }

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
