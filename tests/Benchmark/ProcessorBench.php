<?php
namespace Thunder\Shortcode\Tests\Benchmark;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\HybridParser;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

#[BeforeMethods('setUp')]
#[Revs(1)]
#[Iterations(5)]
#[Warmup(1)]
final class ProcessorBench
{
    private Processor $processor;
    private string $text;
    private int $loops;

    public function setUp(array $params): void
    {
        $handlers = new HandlerContainer();
        $handlers->setDefault(function(ShortcodeInterface $s) {
            return '<'.$s->getName().'>'.$s->getContent().'</'.$s->getName().'>';
        });
        $handlers->add('url', function(ShortcodeInterface $s) {
            return '<a href="'.$s->getParameter('href').'">'.$s->getContent().'</a>';
        });
        $handlers->add('button', function(ShortcodeInterface $s) {
            return '<a class="button '.$s->getParameter('color').'" href="'.$s->getParameter('url').'">'.$s->getContent().'</a>';
        });
        $handlers->add('img', function(ShortcodeInterface $s) {
            return '<img src="'.$s->getParameter('src').'" alt="'.$s->getParameter('alt').'">';
        });
        $handlers->add('icon', function(ShortcodeInterface $s) {
            return '<i class="icon-'.$s->getBbCode().'"></i>';
        });

        $parser = 'hybrid' === $params['parser'] ? new HybridParser() : new RegularParser();
        $this->processor = new Processor($parser, $handlers);
        $this->text = Texts::get($params['text'], $params['times']);
        $this->loops = $params['loops'];
    }

    #[ParamProviders('provideCases')]
    public function benchProcess(): void
    {
        for($i = 0; $i < $this->loops; $i++) {
            $this->processor->process($this->text);
        }
    }

    public function provideCases(): \Generator
    {
        $times = array('article' => 8, 'dense' => 250, 'nested' => 50, 'deep' => 1);
        $loops = array(
            'regular' => array('article' => 50, 'dense' => 2, 'nested' => 10, 'deep' => 2),
            'hybrid' => array('article' => 200, 'dense' => 2, 'nested' => 50, 'deep' => 20),
        );
        foreach($loops as $parser => $texts) {
            foreach($texts as $text => $count) {
                yield $parser.' '.$text => array('parser' => $parser, 'text' => $text, 'times' => $times[$text], 'loops' => $count);
            }
        }
    }
}
