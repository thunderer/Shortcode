<?php
namespace Thunder\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
interface SerializerInterface
    {
    /**
     * Serializes Shortcode class instance into given format
     *
     * @param Shortcode $s Instance to serialize
     *
     * @return string
     */
    public function serialize(Shortcode $s);

    /**
     * Loads back Shortcode instance from serialized format
     *
     * @param $text
     *
     * @return Shortcode
     */
    public function unserialize($text);
    }
