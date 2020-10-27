<?php
namespace Thunder\Shortcode\Syntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SyntaxBuilder
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
     * @param string $tag
     *
     * @return $this
     */
    public function setOpeningTag($tag)
    {
        $this->openingTag = $tag;

        return $this;
    }

    /**
     * @param string $tag
     *
     * @return $this
     */
    public function setClosingTag($tag)
    {
        $this->closingTag = $tag;

        return $this;
    }

    /**
     * @param string $marker
     *
     * @return $this
     */
    public function setClosingTagMarker($marker)
    {
        $this->closingTagMarker = $marker;

        return $this;
    }

    /**
     * @param string $separator
     *
     * @return $this
     */
    public function setParameterValueSeparator($separator)
    {
        $this->parameterValueSeparator = $separator;

        return $this;
    }

    /**
     * @param string $delimiter
     *
     * @return $this
     */
    public function setParameterValueDelimiter($delimiter)
    {
        $this->parameterValueDelimiter = $delimiter;

        return $this;
    }
}
