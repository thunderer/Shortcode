<?php
namespace Thunder\Shortcode\EventHandler;

use Thunder\Shortcode\Event\RewriteReplacementsEvent;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class RewriteWrapEventHandler
{
    /** @var string */
    private $prefix;
    /** @var string */
    private $suffix;

    /**
     * @param string $prefix
     * @param string $suffix
     */
    public function __construct($prefix, $suffix)
    {
        $this->prefix = $prefix;
        $this->suffix = $suffix;
    }

    public function __invoke(RewriteReplacementsEvent $event)
    {
        foreach($event->getReplacements() as $replacement) {
            $event->addRewritten($replacement->withReplacement($this->prefix.$replacement->getReplacement().$this->suffix));
        }
    }
}
