<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class WrapHandler
{
    /** @var string */
    private $before;
    /** @var string */
    private $after;

    /**
     * @param string $before
     * @param string $after
     */
    public function __construct($before, $after)
    {
        $this->before = $before;
        $this->after = $after;
    }

    /** @return self */
    public static function createBold()
    {
        return new self('<b>', '</b>');
    }

    /**
     * [b]content[b]
     * [strong]content[/strong]
     *
     * @param ShortcodeInterface $shortcode
     *
     * @return string
     */
    public function __invoke(ShortcodeInterface $shortcode)
    {
        return $this->before.(string)$shortcode->getContent().$this->after;
    }
}
