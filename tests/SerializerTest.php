<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\SerializerInterface;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SerializerTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @dataProvider provideShortcodes
     */
    public function testSerializer(SerializerInterface $serializer, $text, Shortcode $shortcode)
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
        $content = new Shortcode('x', array('arg' => 'val'), 'cnt');

        return array(
            array(new TextSerializer(), '[x arg=val]', $empty),
            array(new TextSerializer(), '[x arg=val]cnt[/x]', $content),
            array(new JsonSerializer(), '{"name":"x","parameters":{"arg":"val"},"content":null}', $empty),
            array(new JsonSerializer(), '{"name":"x","parameters":{"arg":"val"},"content":"cnt"}', $content),
            );
        }

    public function testExceptionInvalidJson()
        {
        $serializer = new JsonSerializer();
        $this->setExpectedException('RuntimeException');
        $serializer->unserialize('');
        }

    public function testExceptionMalformedJson()
        {
        $serializer = new JsonSerializer();
        $this->setExpectedException('RuntimeException');
        $serializer->unserialize('{}');
        }

    public function testExceptionMalformedText()
        {
        $serializer = new TextSerializer();
        $this->setExpectedException('InvalidArgumentException');
        $serializer->unserialize('[/sc]');
        }

    public function testExceptionMultipleText()
        {
        $serializer = new TextSerializer();
        $this->setExpectedException('InvalidArgumentException');
        $serializer->unserialize('[sc /] c [xx]');
        }
    }
