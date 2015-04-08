<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Syntax;
use Thunder\Shortcode\SyntaxBuilder;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class SyntaxTest extends \PHPUnit_Framework_TestCase
    {
    public function testSyntax()
        {
        $syntax = new Syntax();

        $this->assertSame('[', $syntax->getOpeningTag());
        $this->assertSame(']', $syntax->getClosingTag());
        $this->assertSame('/', $syntax->getClosingTagMarker());
        $this->assertSame('=', $syntax->getParameterValueSeparator());
        $this->assertSame('"', $syntax->getParameterValueDelimiter());
        }

    public function testCustomSyntax()
        {
        $syntax = new Syntax('[[', ']]', '//', '==', '""');

        $this->assertSame('[[', $syntax->getOpeningTag());
        $this->assertSame(']]', $syntax->getClosingTag());
        $this->assertSame('//', $syntax->getClosingTagMarker());
        $this->assertSame('==', $syntax->getParameterValueSeparator());
        $this->assertSame('""', $syntax->getParameterValueDelimiter());
        }

    public function testBuilder()
        {
        $syntax = (new SyntaxBuilder())->getSyntax();

        $this->assertSame('[', $syntax->getOpeningTag());
        $this->assertSame(']', $syntax->getClosingTag());
        $this->assertSame('/', $syntax->getClosingTagMarker());
        $this->assertSame('=', $syntax->getParameterValueSeparator());
        $this->assertSame('"', $syntax->getParameterValueDelimiter());

        $syntax = (new SyntaxBuilder())
            ->setOpeningTag('[[')
            ->setClosingTag(']]')
            ->setClosingTagMarker('//')
            ->setParameterValueSeparator('==')
            ->setParameterValueDelimiter('""')
            ->getSyntax();

        $this->assertSame('[[', $syntax->getOpeningTag());
        $this->assertSame(']]', $syntax->getClosingTag());
        $this->assertSame('//', $syntax->getClosingTagMarker());
        $this->assertSame('==', $syntax->getParameterValueSeparator());
        $this->assertSame('""', $syntax->getParameterValueDelimiter());
        }
    }
