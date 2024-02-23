<?php
namespace Thunder\Shortcode\Event;

use Thunder\Shortcode\Shortcode\ReplacedShortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * This event is called after gathering shortcode handlers results and just
 * before the REPLACE_SHORTCODES event to allow modification of replacements
 * before applying them in the source text.
 *
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class RewriteReplacementsEvent
{
    /** @var ReplacedShortcode[] */
    private $replacements;
    /** @var ReplacedShortcode[] */
    private $rewritten = array();
    /** @var bool */
    private $called = false;

    /** @param ReplacedShortcode[] $replacements */
    public function __construct(array $replacements)
    {
        $this->replacements = $replacements;
    }

    /** @return ReplacedShortcode[] */
    public function getReplacements()
    {
        $this->called = true;

        return $this->replacements;
    }

    /** @return void */
    public function addRewritten(ReplacedShortcode $replacement)
    {
        $this->rewritten[] = $replacement;
    }

    /** @return ReplacedShortcode[] */
    public function getRewritten()
    {
        return $this->called ? $this->rewritten : $this->replacements;
    }
}
