<?php
namespace Thunder\Shortcode\Event;

use Thunder\Shortcode\Shortcode\ReplacedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * This event is called just before returning processed text result at each
 * processing level to alter the way shortcodes are replaced with their handlers
 * results in the source text.
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
class ReplaceShortcodesEvent
{
    /** @var ShortcodeInterface|null */
    private $shortcode;
    /** @var string */
    private $text;
    /** @var ReplacedShortcode[] */
    private $replacements = array();
    /** @var string|null */
    private $result;

    /**
     * @param string $text
     * @param ReplacedShortcode[] $replacements
     */
    public function __construct($text, array $replacements, ShortcodeInterface $shortcode = null)
    {
        $this->shortcode = $shortcode;
        $this->text = $text;

        $this->setReplacements($replacements);
    }

    /**
     * @param ReplacedShortcode[] $replacements
     *
     * @return void
     */
    private function setReplacements(array $replacements)
    {
        foreach($replacements as $replacement) {
            $this->addReplacement($replacement);
        }
    }

    /** @return void */
    private function addReplacement(ReplacedShortcode $replacement)
    {
        $this->replacements[] = $replacement;
    }

    /** @return string */
    public function getText()
    {
        return $this->text;
    }

    /** @return ReplacedShortcode[] */
    public function getReplacements()
    {
        return $this->replacements;
    }

    /** @return ShortcodeInterface|null */
    public function getShortcode()
    {
        return $this->shortcode;
    }

    /**
     * @param string $result
     *
     * @return void
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /** @return string|null */
    public function getResult()
    {
        return $this->result;
    }

    /** @return bool */
    public function hasResult()
    {
        return null !== $this->result;
    }
}
