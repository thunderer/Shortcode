<?php
namespace Thunder\Shortcode\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
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
final class SerializerTest extends AbstractTestCase
{
    /**
     * @dataProvider provideShortcodes
     */
    #[DataProvider('provideShortcodes')]
    public function testSerializer(SerializerInterface $serializer, ShortcodeInterface $test)
    {
        $result = $serializer->serialize($test);
        $tested = $serializer->unserialize($result);

        static::assertSame($test->getName(), $tested->getName(), 'name: '.$result);
        static::assertSame($test->getParameters(), $tested->getParameters(), 'parameters: '.$result);
        static::assertSame($test->getContent(), $tested->getContent(), 'content: '.$result);
        static::assertSame($test->getBbCode(), $tested->getBbCode(), 'bbCode: '.$result);
    }

    public static function provideShortcodes()
    {
        $shortcodes = array(
            new Shortcode('x', array(), null),
            new Shortcode('x', array('arg' => 'val'), null),
            new Shortcode('x', array('arg' => null), null),
            new Shortcode('x', array('arg' => ''), null),
            new Shortcode('x', array('arg' => 'val'), 'cnt'),
            new ParsedShortcode(new Shortcode('self-closed', array(), null), '[self-closed /]', 0),
            new Shortcode('self-closed', array(), null, 'bb code'."\n".' value'),
        );

        $serializers = array(
            new TextSerializer(),
            new JsonSerializer(),
            new XmlSerializer(),
            new YamlSerializer(),
        );

        $tests = array();
        foreach($shortcodes as $shortcode) {
            foreach($serializers as $serializer) {
                $tests[] = array($serializer, $shortcode);
            }
        }

        return $tests;
    }

    /**
     * @dataProvider provideUnserialized
     */
    #[DataProvider('provideUnserialized')]
    public function testUnserialize(SerializerInterface $serializer, ShortcodeInterface $test, $text)
    {
        $tested = $serializer->unserialize($text);

        static::assertSame($test->getName(), $tested->getName(), 'name: '.$text);
        static::assertSame($test->getParameters(), $tested->getParameters(), 'parameters: '.$text);
        static::assertSame($test->getContent(), $tested->getContent(), 'content: '.$text);
        static::assertSame($test->getBbCode(), $tested->getBbCode(), 'bbCode: '.$text);
    }

    public static function provideUnserialized()
    {
        return array(
            array(new JsonSerializer(), new Shortcode('x', array(), null), '{"name":"x"}'),
            array(new JsonSerializer(), new Shortcode('x', array('arg' => 'val'), null), '{"name":"x","parameters":{"arg":"val"}}'),
            array(new JsonSerializer(), new Shortcode('x', array(), 'cnt'), '{"name":"x","content":"cnt"}'),
            array(new YamlSerializer(), new Shortcode('x', array(), null), 'name: x'),
            array(new YamlSerializer(), new Shortcode('x', array('arg' => 'val'), null), 'name: x'."\n".'parameters:'."\n".'  arg: val'),
            array(new YamlSerializer(), new Shortcode('x', array(), 'cnt'), 'name: x'."\n".'content: cnt'),
            array(new XmlSerializer(), new Shortcode('x', array(), null), '<shortcode name="x"></shortcode>'),
            array(new XmlSerializer(), new Shortcode('x', array('arg' => 'val'), null), '<shortcode name="x"><parameters><parameter name="arg">val</parameter></parameters></shortcode>'),
            array(new XmlSerializer(), new Shortcode('x', array(), 'cnt'), '<shortcode name="x"><content>cnt</content></shortcode>'),
        );
    }

    /**
     * @dataProvider provideExceptions
     */
    #[DataProvider('provideExceptions')]
    public function testSerializerExceptions(SerializerInterface $serializer, $value, $exceptionClass)
    {
        $this->willThrowException($exceptionClass);
        $serializer->unserialize($value);
    }

    public static function provideExceptions()
    {
        $xml = new XmlSerializer();
        $yaml = new YamlSerializer();
        $text = new TextSerializer();
        $json = new JsonSerializer();

        return array(
            array($text, '[sc /] c [xx]', 'InvalidArgumentException'),
            array($text, '[/sc]', 'InvalidArgumentException'),
            array($json, '{}', 'InvalidArgumentException'),
            array($json, '', 'InvalidArgumentException'),
            array($json, '{"name":"x","parameters":null}', 'InvalidArgumentException'),
            array($json, '{"name":"x","parameters":{"key":[]}}', 'InvalidArgumentException'),
            array($yaml, 'shortcode: ', 'InvalidArgumentException'),
            array($yaml, '', 'InvalidArgumentException'),
            array($yaml, 'name: x'."\n".'parameters: string', 'InvalidArgumentException'),
            array($xml, '<shortcode />', 'InvalidArgumentException'),
            array($xml, '<shortcode name=""><content>sss</content></shortcode>', 'InvalidArgumentException'),
            array($xml, '<shortcode name="x"><parameters><parameter>xx</parameter></parameters><content>sss</content></shortcode>', 'InvalidArgumentException'),
            array($xml, '<shortcode name="x"><parameters><parameter name="">xx</parameter></parameters><content>sss</content></shortcode>', 'InvalidArgumentException'),
            array($xml, '<shortcode name="x"><parameters><parameter name=>xx</parameter></parameters><content>sss</content></shortcode>', 'InvalidArgumentException'),
            array($xml, '<invalid />', 'InvalidArgumentException'),
            array($xml, '', 'InvalidArgumentException'),
        );
    }

    public function testTextSerializerParametersAndBbCode()
    {
        $serializer = new TextSerializer();

        $this->assertSame('[n=bb a=1 b=2 /]', $serializer->serialize(new Shortcode('n', array('a' => '1', 'b' => '2'), null, 'bb')));
        $this->assertSame('[n a=żółć /]', $serializer->serialize(new Shortcode('n', array('a' => 'żółć'), null)));
    }

    public function testExceptionOnInvalidTextSerializerSyntax()
    {
        $this->willThrowException('LogicException');
        new TextSerializer(new \stdClass());
    }

    public function testXmlSerializerRestoresLibxmlErrorMode()
    {
        $serializer = new XmlSerializer();
        $previous = libxml_use_internal_errors();

        foreach(array(true, false) as $mode) {
            libxml_use_internal_errors($mode);
            try {
                $serializer->unserialize('<shortcode');
            } catch(\InvalidArgumentException $e) {
            }
            $this->assertSame($mode, libxml_use_internal_errors());

            $serializer->unserialize('<shortcode name="a"><parameters/><content/></shortcode>');
            $this->assertSame($mode, libxml_use_internal_errors());
        }

        libxml_use_internal_errors($previous);
    }

    public function testYamlSerializerMalformedException()
    {
        $serializer = new YamlSerializer();

        try {
            $serializer->unserialize("foo: bar\n");
            $this->fail('Expected InvalidArgumentException.');
        } catch(\InvalidArgumentException $e) {
            $this->assertSame('Malformed shortcode YAML, expected name, parameters, and content!', $e->getMessage());
        }
    }

    public function testJsonSerializerMalformedException()
    {
        $serializer = new JsonSerializer();

        try {
            $serializer->unserialize('{}');
            $this->fail('Expected InvalidArgumentException.');
        } catch(\InvalidArgumentException $e) {
            $this->assertSame('Malformed Shortcode JSON, expected name, parameters, and content!', $e->getMessage());
        }
    }

    public function testXmlSerializerInvalidXmlException()
    {
        $serializer = new XmlSerializer();

        try {
            $serializer->unserialize('<invalid');
            $this->fail('Expected InvalidArgumentException.');
        } catch(\InvalidArgumentException $e) {
            $this->assertSame('Failed to parse provided XML!', $e->getMessage());
        }
    }
}
