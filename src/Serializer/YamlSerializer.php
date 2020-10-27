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
        /** @psalm-var array{name:string,parameters:array<string,string|null>,bbCode:string|null,content:string|null}|null $data */
        $data = Yaml::parse($text);

        if(!is_array($data)) {
            throw new \InvalidArgumentException('Invalid YAML, cannot unserialize Shortcode!');
        }
        if (!array_intersect(array_keys($data), array('name', 'parameters', 'content'))) {
            throw new \InvalidArgumentException('Malformed shortcode YAML, expected name, parameters, and content!');
        }

        /** @var string $name */
        $name = array_key_exists('name', $data) ? $data['name'] : null;
        $parameters = array_key_exists('parameters', $data) ? $data['parameters'] : array();
        $content = array_key_exists('content', $data) ? $data['content'] : null;
        $bbCode = array_key_exists('bbCode', $data) ? $data['bbCode'] : null;

        /** @psalm-suppress DocblockTypeContradiction */
        if(!is_array($parameters)) {
            throw new \InvalidArgumentException('Parameters must be an array!');
        }

        return new Shortcode($name, $parameters, $content, $bbCode);
    }
}
