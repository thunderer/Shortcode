<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SyntaxBuilder
    {
    private $openingTag;
    private $closingTag;
    private $closingTagMarker;
    private $parameterValueSeparator;
    private $parameterValueDelimiter;
    private $isStrict = false;

    public function __construct()
        {
        }

    public function getSyntax()
        {
        $args = array($this->openingTag, $this->closingTag, $this->closingTagMarker,
            $this->parameterValueSeparator, $this->parameterValueDelimiter);
        $method = $this->isStrict ? 'createStrict' : 'create';

        return call_user_func_array(array('Thunder\Shortcode\Syntax', $method), $args);
        }

    public function setStrict($isStrict)
        {
        $this->isStrict = (bool)$isStrict;

        return $this;
        }

    public function setOpeningTag($tag)
        {
        $this->openingTag = $tag;

        return $this;
        }

    public function setClosingTag($tag)
        {
        $this->closingTag = $tag;

        return $this;
        }

    public function setClosingTagMarker($marker)
        {
        $this->closingTagMarker = $marker;

        return $this;
        }

    public function setParameterValueSeparator($separator)
        {
        $this->parameterValueSeparator = $separator;

        return $this;
        }

    public function setParameterValueDelimiter($delimiter)
        {
        $this->parameterValueDelimiter = $delimiter;

        return $this;
        }
    }
