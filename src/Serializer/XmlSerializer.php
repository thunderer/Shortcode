<?php
namespace Thunder\Shortcode\Serializer;

use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class XmlSerializer implements SerializerInterface
{
    /**
     * <shortcode name="NAME">
     *   <bbCode>BBCODE</bbCode>
     *   <parameters>
     *     <parameter name="KEY">VALUE</parameter>
     *     <parameter name="KEY">VALUE</parameter>
     *   </parameters>
     *   <content>CONTENT></content>
     * </shortcode>
     *
     * @param ShortcodeInterface $shortcode
     *
     * @return string
     */
    public function serialize(ShortcodeInterface $shortcode)
    {
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        $code = $doc->createElement('shortcode');
        // @mago-expect analysis:invalid-method-access
        $code->setAttribute('name', $shortcode->getName());
        // @mago-expect analysis:possibly-false-argument
        $xml = $doc->appendChild($code);
        // @mago-expect analysis:invalid-method-access
        $xml->appendChild($this->createCDataNode($doc, 'bbCode', $shortcode->getBbCode()));

        // @mago-expect analysis:invalid-method-access, analysis:possibly-false-argument
        $parameters = $xml->appendChild($doc->createElement('parameters'));
        foreach($shortcode->getParameters() as $key => $value) {
            $parameter = $doc->createElement('parameter');
            // @mago-expect analysis:invalid-method-access
            $parameter->setAttribute('name', $key);
            if(null !== $value) {
                // @mago-expect analysis:invalid-method-access, analysis:possibly-false-argument
                $parameter->appendChild($doc->createCDATASection($value));
            }

            // @mago-expect analysis:invalid-method-access, analysis:possibly-false-argument
            $parameters->appendChild($parameter);
        }

        // @mago-expect analysis:invalid-method-access
        $xml->appendChild($this->createCDataNode($doc, 'content', $shortcode->getContent()));

        // @mago-expect analysis:falsable-return-statement, analysis:invalid-return-statement
        return $doc->saveXML();
    }

    /**
     * @param \DOMDocument $doc
     * @param string $name
     * @param string|null $content
     *
     * @return \DOMElement
     */
    private function createCDataNode(\DOMDocument $doc, $name, $content)
    {
        $node = $doc->createElement($name);

        if(null !== $content) {
            // @mago-expect analysis:invalid-method-access, analysis:possibly-false-argument
            $node->appendChild($doc->createCDATASection($content));
        }

        // @mago-expect analysis:falsable-return-statement, analysis:invalid-return-statement
        return $node;
    }

    /**
     * @param string $text
     *
     * @return Shortcode
     */
    public function unserialize($text)
    {
        $xml = new \DOMDocument();
        $internalErrors = libxml_use_internal_errors(true);
        if(!$text || !$xml->loadXML($text)) {
            libxml_use_internal_errors($internalErrors);
            throw new \InvalidArgumentException('Failed to parse provided XML!');
        }
        libxml_use_internal_errors($internalErrors);

        $xpath = new \DOMXPath($xml);
        // @mago-expect analysis:mixed-assignment
        $shortcode = $xpath->query('/shortcode');
        // @mago-expect analysis:mixed-property-access
        if($shortcode->length !== 1) {
            throw new \InvalidArgumentException('Invalid shortcode XML!');
        }
        /** @psalm-suppress PossiblyNullArgument */
        // @mago-expect analysis:mixed-method-access, analysis:mixed-argument
        $name = $this->getAttribute($shortcode->item(0), 'name');

        // @mago-expect analysis:mixed-argument
        $bbCode = $this->getValue($xpath->query('/shortcode/bbCode'));
        // @mago-expect analysis:mixed-argument
        $content = $this->getValue($xpath->query('/shortcode/content'));

        $parameters = array();
        // @mago-expect analysis:mixed-assignment
        $elements = $xpath->query('/shortcode/parameters/parameter');
        // @mago-expect analysis:mixed-operand
        // @mago-expect analysis:mixed-property-access
        for($i = 0; $i < $elements->length; $i++) {
            // @mago-expect analysis:mixed-method-access, analysis:mixed-assignment
            $node = $elements->item($i);

            /** @psalm-suppress PossiblyNullReference */
            // @mago-expect analysis:mixed-method-access, analysis:mixed-property-access, analysis:mixed-argument
            $parameters[$this->getAttribute($node, 'name')] = $node->hasChildNodes() ? $node->nodeValue : null;
        }

        // @mago-expect analysis:less-specific-nested-argument-type
        return new Shortcode($name, $parameters, $content, $bbCode);
    }

    /**
     * @param \DOMNodeList $node
     *
     * @return string|null
     */
    private function getValue(\DOMNodeList $node)
    {
        /** @psalm-suppress PossiblyNullReference,PossiblyNullPropertyFetch */
        // @mago-expect analysis:possible-method-access-on-null, analysis:non-existent-method
        return $node->length === 1 && $node->item(0)->hasChildNodes()
            // @mago-expect analysis:possibly-null-property-access
            ? $node->item(0)->nodeValue
            : null;
    }

    /**
     * @param \DOMNode $node
     * @param string $name
     *
     * @return string
     */
    private function getAttribute(\DOMNode $node, $name)
    {
        /**
         * @var \DOMNode $attribute
         * @psalm-suppress NullReference
         */
        // @mago-expect analysis:possible-method-access-on-null
        $attribute = $node->attributes->getNamedItem($name);

        /** @psalm-suppress DocblockTypeContradiction,RiskyTruthyFalsyComparison */
        // @mago-expect analysis:redundant-condition, analysis:redundant-logical-operation
        if(!$attribute || !$attribute->nodeValue) {
            throw new \InvalidArgumentException('Invalid shortcode XML!');
        }

        return $attribute->nodeValue;
    }
}
