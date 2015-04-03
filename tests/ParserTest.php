<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Parser;
use Thunder\Shortcode\Shortcode;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class ParserTest extends \PHPUnit_Framework_TestCase
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

        $parser = new Parser();
        $parser->addCode('sc', function(Shortcode $s) use(&$parsedName, &$parsedArgs, &$parsedContent) {
            $parsedName = $s->getName();
            $parsedArgs = $s->getParameters();
            $parsedContent = $s->getContent();
            return '';
            });
        $return = $parser->parse($code);

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
        $parser = new Parser();
        $parser->addCode('name', function(Shortcode $s) { return $s->getName(); });
        $parser->addCode('args', function(Shortcode $s) { return implode(',', array_keys($s->getParameters())); });
        $parser->addCode('content', function(Shortcode $s) { return $s->getContent(); });

        $this->assertSame('random name string', $parser->parse('random [name] string'));
        $this->assertSame('random noval,arg string', $parser->parse('random [args noval arg=var] string'));
        $this->assertSame('random otherwise string', $parser->parse('random [content]otherwise[/content] string'));
        }

    public function testTreatShortcodeAsEmptyWhenClosingTagMismatch()
        {
        $parser = new Parser();
        $parser->addCode('name', function(Shortcode $s) { return $s->getName().'_'; });

        $this->assertSame('name_content[/other]', $parser->parse('[name noval]content[/other]'));
        }

    public function testExceptionOnDuplicateCodeName()
        {
        $parser = new Parser();
        $parser->addCode('name', function() {});
        $this->setExpectedException('RuntimeException');
        $parser->addCode('name', function() {});
        }

    public function testReturnRawStringForUnknownCode()
        {
        $parser = new Parser();
        $this->assertEquals('[unknown]', $parser->parse('[unknown]'));
        }
    }
