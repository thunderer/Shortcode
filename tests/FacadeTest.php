<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\ShortcodeFacade;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class FacadeTest extends \PHPUnit_Framework_TestCase
    {
    public function testFacade()
        {
        $facade = ShortcodeFacade::create(null, array(
            'name' => function(ShortcodeInterface $s) { return $s->getName(); },
            'content' => function(ShortcodeInterface $s) { return $s->getContent(); },
            ), array(
            'c' => 'content',
            'n' => 'name',
            ));

        $this->assertSame('n', $facade->process('[n]'));
        $this->assertSame('c', $facade->process('[c]c[/c]'));

        $this->assertCount(1, $facade->extract('[x]'));
        $this->assertCount(2, $facade->extract('[x]x[y]'));

        $this->assertInstanceOf('Thunder\\Shortcode\\Shortcode\\Shortcode', $facade->parse('[b]'));

        $s = new Shortcode('c', array(), null);
        $this->assertSame('[c]', $facade->serializeToText($s));
        $this->assertSame('c', $facade->unserializeFromText('[c]')->getName());

        $json = '{"name":"c","parameters":[],"content":null}';
        $this->assertSame($json, $facade->serializeToJson($s));
        $this->assertSame('c', $facade->unserializeFromJson($json)->getName());
        }
    }
