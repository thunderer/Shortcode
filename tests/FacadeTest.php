<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Shortcode\Shortcode;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\ShortcodeFacade;
use Thunder\Shortcode\Syntax\CommonSyntax;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class FacadeTest extends \PHPUnit_Framework_TestCase
{
    public function testFacade()
    {
        $handlers = new HandlerContainer();
        $handlers
            ->add('name', function (ShortcodeInterface $s) { return $s->getName(); })
            ->add('content', function (ShortcodeInterface $s) { return $s->getContent(); })
            ->addAlias('c', 'content')
            ->addAlias('n', 'name');

        $facade = ShortcodeFacade::create($handlers, new CommonSyntax());

        $this->assertSame('n', $facade->process('[n]'));
        $this->assertSame('c', $facade->process('[c]c[/c]'));

        $shortcodes = $facade->parse('[b]');
        $this->assertInstanceOf('Thunder\\Shortcode\\Shortcode\\ShortcodeInterface', $shortcodes[0]);

        $s = new Shortcode('c', array(), null);
        $this->assertSame('[c /]', $facade->serializeToText($s));
        $this->assertSame('c', $facade->unserializeFromText('[c]')->getName());

        $json = '{"name":"c","parameters":[],"content":null,"bbCode":null}';
        $this->assertSame($json, $facade->serializeToJson($s));
        $this->assertSame('c', $facade->unserializeFromJson($json)->getName());
    }
}
