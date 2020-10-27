<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class Syntax implements SyntaxInterface
{
    /** @var string|null */
    private $openingTag;
    /** @var string|null */
    private $closingTag;
    /** @var string|null */
    private $closingTagMarker;
    /** @var string|null */
    private $parameterValueSeparator;
    /** @var string|null */
    private $parameterValueDelimiter;

    /**
     * @param string|null $openingTag
     * @param string|null $closingTag
     * @param string|null $closingTagMarker
     * @param string|null $parameterValueSeparator
     * @param string|null $parameterValueDelimiter
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

    /** @return string */
    public function getOpeningTag()
    {
        return $this->openingTag ?: '[';
    }

    /** @return string */
    public function getClosingTag()
    {
        return $this->closingTag ?: ']';
    }

    /** @return string */
    public function getClosingTagMarker()
    {
        return $this->closingTagMarker ?: '/';
    }

    /** @return string */
    public function getParameterValueSeparator()
    {
        return $this->parameterValueSeparator ?: '=';
    }

    /** @return string */
    public function getParameterValueDelimiter()
    {
        return $this->parameterValueDelimiter ?: '"';
    }
}
