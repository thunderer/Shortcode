<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\SerializerInterface;
use Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SerializerTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @param SerializerInterface $serializer
     * @param $text
     * @param Shortcode $shortcode
     *
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
        return array(
            array(new TextSerializer(), '[x arg=val]', new Shortcode('x', array('arg' => 'val'), null)),
            array(new TextSerializer(), '[x arg=val]cnt[/x]', new Shortcode('x', array('arg' => 'val'), 'cnt')),
            array(new JsonSerializer(), '{"name":"x","parameters":{"arg":"val"},"content":"cnt"}',
                new Shortcode('x', array('arg' => 'val'), 'cnt')),
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
    }
