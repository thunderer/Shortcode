<?php
namespace Thunder\Shortcode\Tests;

use Thunder\Shortcode\Syntax;
use Thunder\Shortcode\SyntaxBuilder;
use Thunder\Shortcode\Syntax\StandardSyntax;

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

    public function testStandardSyntax()
        {
        $syntax = new StandardSyntax();

        $this->assertSame('[', $syntax->getOpeningTag());
        $this->assertSame(']', $syntax->getClosingTag());
        $this->assertSame('/', $syntax->getClosingTagMarker());
        $this->assertSame('=', $syntax->getParameterValueSeparator());
        $this->assertSame('"', $syntax->getParameterValueDelimiter());
        }

    /**
     * @deprecated Will be removed with obsolete named constructors from Syntax class
     */
    public function testSyntaxWithNamedConstructor()
        {
        $syntax = Syntax::create();

        $this->assertSame('[', $syntax->getOpeningTag());
        $this->assertSame(']', $syntax->getClosingTag());
        $this->assertSame('/', $syntax->getClosingTagMarker());
        $this->assertSame('=', $syntax->getParameterValueSeparator());
        $this->assertSame('"', $syntax->getParameterValueDelimiter());

        $syntax = Syntax::createStrict();

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
        $builder = new SyntaxBuilder();
        $syntax = $builder->getSyntax();

        $this->assertSame('[', $syntax->getOpeningTag());
        $this->assertSame(']', $syntax->getClosingTag());
        $this->assertSame('/', $syntax->getClosingTagMarker());
        $this->assertSame('=', $syntax->getParameterValueSeparator());
        $this->assertSame('"', $syntax->getParameterValueDelimiter());

        $builder = new SyntaxBuilder();
        $syntax = $builder
            ->setOpeningTag('[[')
            ->setClosingTag(']]')
            ->setClosingTagMarker('//')
            ->setParameterValueSeparator('==')
            ->setParameterValueDelimiter('""')
            ->setStrict(true)
            ->getSyntax();

        $this->assertSame('[[', $syntax->getOpeningTag());
        $this->assertSame(']]', $syntax->getClosingTag());
        $this->assertSame('//', $syntax->getClosingTagMarker());
        $this->assertSame('==', $syntax->getParameterValueSeparator());
        $this->assertSame('""', $syntax->getParameterValueDelimiter());
        }
    }
