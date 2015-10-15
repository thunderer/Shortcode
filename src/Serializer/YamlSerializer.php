<?php
namespace Thunder\Shortcode\Serializer;

use Symfony\Component\Yaml\Yaml;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class YamlSerializer implements SerializerInterface
{
    public function serialize(ShortcodeInterface $shortcode)
    {
        return Yaml::dump(array(
            'name' => $shortcode->getName(),
            'parameters' => $shortcode->getParameters(),
            'content' => $shortcode->getContent(),
            'bbCode' => $shortcode->getBbCode(),
        ));
    }

    /**
     * @param string $text
     *
     * @return Shortcode
     */
    public function unserialize($text)
    {
        $data = Yaml::parse($text);

        if(!$data || !is_array($data)) {
            throw new \InvalidArgumentException('Invalid YAML, cannot unserialize Shortcode!');
        }
        if (!array_intersect(array_keys($data), array('name', 'parameters', 'content'))) {
            throw new \InvalidArgumentException('Malformed shortcode YAML, expected name, parameters, and content!');
        }

        return new Shortcode($data['name'], $data['parameters'], $data['content'], $data['bbCode']);
    }
}
