<?php
namespace Thunder\Shortcode\Serializer;

use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

final class XmlSerializer implements SerializerInterface
{
    public function serialize(ShortcodeInterface $shortcode)
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        $xml = $doc->appendChild($doc->createElement('shortcode'));
        $xml->appendChild($doc->createElement('name', $shortcode->getName()));

        $parameters = $xml->appendChild($doc->createElement('parameters'));
        foreach($shortcode->getParameters() as $key => $value) {
            $parameter = $doc->createElement('parameter');
            $parameter->appendChild($doc->createElement('name', $key));
            $parameter->appendChild($doc->createElement('value', $value));
            $parameters->appendChild($parameter);
        }

        $xml->appendChild($doc->createElement('content', $shortcode->getContent()));

        return $doc->saveXML();
    }

    /**
     * @param string $text
     *
     * @return Shortcode
     */
    public function unserialize($text)
    {
        $xml = new \DOMDocument();
        if(!$text || ($text && !$xml->loadXML($text))) {
            throw new \InvalidArgumentException('Failed to parse provided XML!');
        }

        $xpath = new \DOMXPath($xml);
        $name = $this->getValue($xpath, '/shortcode/name');
        $content = $this->getValue($xpath, '/shortcode/content');

        $parameters = array();
        $elements = $xpath->query('/shortcode/parameters/parameter');
        for($i = 1; $i <= $elements->length; $i++) {
            $path = '/shortcode/parameters/parameter['.$i.']';
            $parameters[$this->getValue($xpath, $path.'/name')] = $this->getValue($xpath, $path.'/value');
        }

        return new Shortcode($name, $parameters, $content);
    }

    private function getValue(\DOMXPath $xpath, $path)
    {
        $node = $xpath->query($path);
        if(1 !== $node->length) {
            throw new \InvalidArgumentException('Invalid shortcode XML!');
        }

        return $node->item(0)->nodeValue;
    }
}
