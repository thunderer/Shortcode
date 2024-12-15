<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Syntax implements SyntaxInterface
{
    /** @var non-empty-string|null */
    private $openingTag;
    /** @var non-empty-string|null */
    private $closingTag;
    /** @var non-empty-string|null */
    private $closingTagMarker;
    /** @var non-empty-string|null */
    private $parameterValueSeparator;
    /** @var non-empty-string|null */
    private $parameterValueDelimiter;

    /**
     * @param non-empty-string|null $openingTag
     * @param non-empty-string|null $closingTag
     * @param non-empty-string|null $closingTagMarker
     * @param non-empty-string|null $parameterValueSeparator
     * @param non-empty-string|null $parameterValueDelimiter
     */
    public function __construct(
        $openingTag = null,
        $closingTag = null,
        $closingTagMarker = null,
        $parameterValueSeparator = null,
        $parameterValueDelimiter = null
    ) {
        $this->openingTag = $openingTag;
        $this->closingTag = $closingTag;
        $this->closingTagMarker = $closingTagMarker;
        $this->parameterValueSeparator = $parameterValueSeparator;
        $this->parameterValueDelimiter = $parameterValueDelimiter;
    }

    /** @return non-empty-string */
    public function getOpeningTag()
    {
        return null !== $this->openingTag ? $this->openingTag : '[';
    }

    /** @return non-empty-string */
    public function getClosingTag()
    {
        return null !== $this->closingTag ? $this->closingTag : ']';
    }

    /** @return non-empty-string */
    public function getClosingTagMarker()
    {
        return null !== $this->closingTagMarker ? $this->closingTagMarker : '/';
    }

    /** @return non-empty-string */
    public function getParameterValueSeparator()
    {
        return null !== $this->parameterValueSeparator ? $this->parameterValueSeparator : '=';
    }

    /** @return non-empty-string */
    public function getParameterValueDelimiter()
    {
        return null !== $this->parameterValueDelimiter ? $this->parameterValueDelimiter : '"';
    }
}
