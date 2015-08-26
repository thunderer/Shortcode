<?php
namespace Thunder\Shortcode\Handler;

use Thunder\Shortcode\Serializer\SerializerInterface;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SerializerHandler
{
    /** @var SerializerInterface */
    private $serializer;

    function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function __invoke(ShortcodeInterface $shortcode)
    {
        return $this->serializer->serialize($shortcode);
    }
}
