<?php
namespace Thunder\Shortcode\Tests\Benchmark;

final class Texts
{
    private const PROSE = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.';
    private const PROSE_UTF8 = 'Zażółć gęślą jaźń, pchnąć w tę łódź jeża lub ośm skrzyń fig. Świątecznie przyozdobiony żółty żuraw ćwiczył źrebięta na łące 🐎, a wąż Ździś łaził między łąkami, gdzie rósł bób. Grzegorz Brzęczyszczykiewicz z Chrząszczyżewoszyc wsiadł do pociągu, żeby dojechać na czas.';

    public static function get(string $name, int $times): string
    {
        return str_repeat(match($name) {
            'article' => self::PROSE.' This is [b]important[/b], so [url href="https://example.com/articles/42"]read more[/url].'."\n\n"
                .self::PROSE_UTF8."\n\n"
                .'[img src="/media/photo-42.jpg" alt="Zdjęcie 📷" /]'."\n\n"
                .'[quote author="Grzegorz Brzęczyszczykiewicz"]'.self::PROSE.'[/quote]'."\n\n",
            'dense' => '[button url="https://example.com/buy?id=42" color=blue size=large]Buy now[/button] [icon=star /] [tag]php[/tag] [img src=photo.jpg alt="A photo" width=640 /]'."\n",
            'nested' => '[row][col width=6][card title="First card"][b]Bold[/b] and [i]italic[/i] text.[/card][/col][col width=6][card title="Second card"][url href="https://example.com"]A link[/url][/card][/col][/row]'."\n",
            'deep' => self::deep(100),
            'brackets' => 'As shown in the survey[1], most results [citation needed] were confirmed later[2]; see [the documentation](https://example.com/docs) for details. ',
        }, $times);
    }

    private static function deep(int $depth): string
    {
        $open = '';
        $close = '';
        for($i = 1; $i <= $depth; $i++) {
            $open .= '[d'.$i.']';
            $close = '[/d'.$i.']'.$close;
        }

        return $open.'x'.$close."\n";
    }
}
