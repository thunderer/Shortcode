<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Shortcode;

final class ShortcodeTest extends \PHPUnit_Framework_TestCase
    {
    /**
     * @param string $code
     * @param string $name
     * @param array $args
     * @param string $content
     *
     * @dataProvider provideShortcodes
     */
    public function testShortcode($code, $name, array $args, $content)
        {
        $parsedName = null;
        $parsedArgs = null;
        $parsedContent = null;

        $sc = new Shortcode();
        $sc->addCode('sc', function($name, array $args, $content) use(&$parsedName, &$parsedArgs, &$parsedContent) {
            $parsedName = $name;
            $parsedArgs = $args;
            $parsedContent = $content;
            return '';
            });
        $return = $sc->parse($code);

        $this->assertEmpty($return, sprintf('Expected empty return, got "%s"!', $return));
        $this->assertSame($name, $parsedName);
        $this->assertSame($args, $parsedArgs);
        $this->assertSame($content, $parsedContent);
        }

    public function provideShortcodes()
        {
        return array(
            array('[sc]', 'sc', array(), null),
            array('[sc]', 'sc', array(), null),
            array('[sc arg=val]', 'sc', array('arg' => 'val'), null),
            array('[sc novalue arg="complex value"]', 'sc', array('novalue' => null, 'arg' => 'complex value'), null),
            array('[sc x="ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ"]', 'sc', array('x' => 'ąćęłńóśżź ĄĆĘŁŃÓŚŻŹ'), null),
            array('[sc x="multi'."\n".'line"]', 'sc', array('x' => 'multi'."\n".'line'), null),
            array('[sc noval x="val" y]content[/sc]', 'sc', array('noval'=> null, 'x' => 'val', 'y' => null), 'content'),
            array('[sc x="{..}"]', 'sc', array('x' => '{..}'), null),
            );
        }

    public function testReplace()
        {
        $sc = new Shortcode();
        $sc->addCode('name', function($name, array $args, $content) { return $name; });
        $sc->addCode('args', function($name, array $args, $content) { return implode(',', array_keys($args)); });
        $sc->addCode('content', function($name, array $args, $content) { return $content; });

        $this->assertSame('random name string', $sc->parse('random [name] string'));
        $this->assertSame('random noval,arg string', $sc->parse('random [args noval arg=var] string'));
        $this->assertSame('random otherwise string', $sc->parse('random [content]otherwise[/content] string'));
        }

    public function testTreatShortcodeAsEmptyWhenClosingTagMismatch()
        {
        $sc = new Shortcode();
        $sc->addCode('name', function($name, array $args, $content) { return $name; });

        $this->assertSame('namecontent[/other]', $sc->parse('[name noval]content[/other]'));
        }

    public function testExceptionOnDuplicateCodeName()
        {
        $sc = new Shortcode();
        $sc->addCode('name', function() {});
        $this->setExpectedException('RuntimeException');
        $sc->addCode('name', function() {});
        }

    public function testReturnRawStringForUnknownCode()
        {
        $sc = new Shortcode();
        $this->assertEquals('[unknown]', $sc->parse('[unknown]'));
        }
    }
