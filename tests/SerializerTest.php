<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\SerializerInterface;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Serializer\XmlSerializer;
use Thunder\Shortcode\Serializer\YamlSerializer;
use Thunder\Shortcode\Shortcode\ParsedShortcode;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideShortcodes
     */
    public function testSerializer(SerializerInterface $serializer, $text, ShortcodeInterface $shortcode)
    {
        $serialized = $serializer->serialize($shortcode);
        $this->assertSame($text, $serialized);

        $s = $serializer->unserialize($text);
        $this->assertSame($shortcode->getName(), $s->getName());
        $this->assertSame($shortcode->getParameters(), $s->getParameters());
        $this->assertSame($shortcode->getContent(), $s->getContent());
    }

    public function provideShortcodes()
    {
        $empty = new Shortcode('x', array('arg' => 'val'), null);
        $nullArgument = new Shortcode('x', array('arg' => null), null);
        $content = new Shortcode('x', array('arg' => 'val'), 'cnt');

        $yaml = <<<EOF
name: x
parameters:
    arg: val
content: cnt

EOF;
        $xml = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<shortcode>
  <name>x</name>
  <parameters>
    <parameter>
      <name>arg</name>
      <value>val</value>
    </parameter>
  </parameters>
  <content>cnt</content>
</shortcode>

EOF;


        return array(
            array(new TextSerializer(), '[x arg=val /]', $empty),
            array(new TextSerializer(), '[x arg /]', $nullArgument),
            array(new TextSerializer(), '[x arg=val]cnt[/x]', $content),
            array(new TextSerializer(), '[self-closed /]', new ParsedShortcode(new Shortcode('self-closed', array(), null), '[self-closed /]', 0, array())),
            array(new JsonSerializer(), '{"name":"x","parameters":{"arg":"val"},"content":null}', $empty),
            array(new JsonSerializer(), '{"name":"x","parameters":{"arg":"val"},"content":"cnt"}', $content),
            array(new XmlSerializer(), $xml, $content),
            array(new YamlSerializer(), $yaml, $content),
            );
    }

    /**
     * @dataProvider provideExceptions
     */
    public function testSerializerExceptions(SerializerInterface $serializer, $value, $exceptionClass)
    {
        $this->setExpectedException($exceptionClass);
        $serializer->unserialize($value);
    }

    public function provideExceptions()
    {
        $xml = new XmlSerializer();
        $yaml = new YamlSerializer();
        $text = new TextSerializer();
        $json = new JsonSerializer();

        return array(
            array($text, '[sc /] c [xx]', 'InvalidArgumentException'),
            array($text, '[/sc]', 'InvalidArgumentException'),
            array($json, '{}', 'RuntimeException'),
            array($json, '', 'RuntimeException'),
            array($yaml, 'shortcode: ', 'InvalidArgumentException'),
            array($yaml, '', 'InvalidArgumentException'),
            array($xml, '<shortcode />', 'InvalidArgumentException'),
            array($xml, '', 'InvalidArgumentException'),
        );
    }
}
