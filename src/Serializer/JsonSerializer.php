<?php
namespace Thunder\Shortcode\Serializer;

use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

final class JsonSerializer implements SerializerInterface
    {
    public function serialize(ShortcodeInterface $s)
        {
        return json_encode(array(
            'name' => $s->getName(),
            'parameters' => $s->getParameters(),
            'content' => $s->getContent(),
            ));
        }

    /**
     * @param string $text
     *
     * @return Shortcode
     */
    public function unserialize($text)
        {
        $data = json_decode($text, true);

        if(!is_array($data))
            {
            throw new \RuntimeException('Invalid JSON, cannot unserialize Shortcode!');
            }
        if(!array_diff_key($data, array('name', 'parameters', 'content')))
            {
            throw new \RuntimeException('Malformed Shortcode JSON, expected name, parameters, and content!');
            }

        return new Shortcode($data['name'], $data['parameters'], $data['content']);
        }
    }
