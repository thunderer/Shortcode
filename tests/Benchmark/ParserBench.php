<?php
namespace Thunder\Shortcode\Tests\Benchmark;

use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Thunder\Shortcode\Parser\HybridParser;
use Thunder\Shortcode\Parser\ParserInterface;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Parser\WordpressParser;

#[BeforeMethods('setUp')]
#[Revs(1)]
#[Iterations(5)]
#[Warmup(1)]
final class ParserBench
{
    private ParserInterface $parser;
    private string $text;
    private int $loops;

    public function setUp(array $params): void
    {
        $this->parser = match($params['parser']) {
            'regular' => new RegularParser(),
            'regex' => new RegexParser(),
            'hybrid' => new HybridParser(),
            'wordpress' => new WordpressParser(),
        };
        $this->text = Texts::get($params['text'], $params['times']);
        $this->loops = $params['loops'];
    }

    #[ParamProviders('provideCases')]
    public function benchParse(): void
    {
        for($i = 0; $i < $this->loops; $i++) {
            $this->parser->parse($this->text);
        }
    }

    public function provideCases(): \Generator
    {
        $times = array('article' => 8, 'dense' => 60, 'nested' => 50, 'deep' => 10, 'brackets' => 200);
        $loops = array(
            'regular' => array('article' => 100, 'dense' => 50, 'nested' => 50, 'deep' => 5, 'brackets' => 10),
            'regex' => array('article' => 2000, 'dense' => 200, 'nested' => 1000, 'deep' => 2000, 'brackets' => 10),
            'hybrid' => array('article' => 2000, 'dense' => 200, 'nested' => 500, 'deep' => 200, 'brackets' => 200),
            'wordpress' => array('article' => 2000, 'dense' => 500, 'nested' => 2000, 'deep' => 5000, 'brackets' => 20),
        );
        foreach($loops as $parser => $texts) {
            foreach($texts as $text => $count) {
                yield $parser.' '.$text => array('parser' => $parser, 'text' => $text, 'times' => $times[$text], 'loops' => $count);
            }
        }
    }
}
