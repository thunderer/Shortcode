<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SyntaxBuilder
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

    public function __construct()
    {
    }

    /** @return Syntax */
    public function getSyntax()
    {
        return new Syntax(
            $this->openingTag,
            $this->closingTag,
            $this->closingTagMarker,
            $this->parameterValueSeparator,
            $this->parameterValueDelimiter
        );
    }

    /**
     * @param non-empty-string $tag
     *
     * @return $this
     */
    public function setOpeningTag($tag)
    {
        $this->openingTag = $tag;

        return $this;
    }

    /**
     * @param non-empty-string $tag
     *
     * @return $this
     */
    public function setClosingTag($tag)
    {
        $this->closingTag = $tag;

        return $this;
    }

    /**
     * @param non-empty-string $marker
     *
     * @return $this
     */
    public function setClosingTagMarker($marker)
    {
        $this->closingTagMarker = $marker;

        return $this;
    }

    /**
     * @param non-empty-string $separator
     *
     * @return $this
     */
    public function setParameterValueSeparator($separator)
    {
        $this->parameterValueSeparator = $separator;

        return $this;
    }

    /**
     * @param non-empty-string $delimiter
     *
     * @return $this
     */
    public function setParameterValueDelimiter($delimiter)
    {
        $this->parameterValueDelimiter = $delimiter;

        return $this;
    }
}
