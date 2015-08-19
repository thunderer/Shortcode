# Shortcode

[![Build Status](https://travis-ci.org/thunderer/Shortcode.png?branch=master)](https://travis-ci.org/thunderer/Shortcode)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d/mini.png)](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d)
[![License](https://poser.pugx.org/thunderer/shortcode/license.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Latest Stable Version](https://poser.pugx.org/thunderer/shortcode/v/stable.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Dependency Status](https://www.versioneye.com/user/projects/551d5385971f7847ca000002/badge.svg?style=flat)](https://www.versioneye.com/user/projects/551d5385971f7847ca000002)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Climate](https://codeclimate.com/github/thunderer/Shortcode/badges/gpa.svg)](https://codeclimate.com/github/thunderer/Shortcode)

Shortcode is a framework and library agnostic engine for interpreting and processing "shortcodes" (small script-like text fragments) using dynamic callbacks. It can be used to create dynamic content replacement mechanism that is usable even by non-technical people without much training. Usual syntax of shortcodes is as shown in the examples below:

```
[shortcode]
[shortcode argument="value"]
[shortcode novalue argument=simple other="complex value"]
[shortcode]content[/shortcode]
[shortcode argument="value"]content[/shortcode]
```

All those variants (and many more, see the tests) are supported.

## Requirements

No required dependencies, only PHP >=5.3

## Installation

To install it from Packagist execute

```
composer require thunderer/shortcode
```

in your terminal or manually update your `composer.json` with

```
(...)
"require": {
    "thunderer/shortcode": "dev-master"
}
(...)
```

and run `composer install` or `composer update` afterwards. If you're not using Composer, download sources from GitHub and load them as required. But really, please use Composer.

## Usage

**Facade**

There is a facade that contains shortcuts to all features in the library. You can instantiate it by using named constructor `ShortcodeFacade::create()` and pass optional `Syntax` object and arrays of shortcode handlers and aliases:

```php
$handlers = (new HandlerContainer())
    ->add('name', function(ShortcodeInterface $s) { return $s->getName(); })
    ->add('content', function(ShortcodeInterface $s) { return $s->getContent(); })
    ->addAlias('n', 'name')
    ->addAlias('c', 'content');
$facade = ShortcodeFacade::create($handlers, new CommonSyntax());

$matches = $facade->extract('[c]');
$shortcode = $facade->parse('[c]');
$result = $facade->process('[c]');

$s = new Shortcode('c', array(), null);
$text = $facade->serializeToText($s);
$shortcode = $facade->unserializeFromText('[c]');
$json = $facade->serializeToJson($s);
$shortcode = $facade->unserializeFromJson('{"name":"c","parameters":[],"content":null}');
```

All those calls are equivalent to the examples below. If you want to change the dependencies, extend `ShortcodeFacade` class and replace them by overloading protected `create*` methods.

**Replacement**

Create `Processor` class instance with required shortcodes handlers and use `process()` method to dynamically replace found matches using registered callbacks:

```php
$handlers = new HandlerContainer();
$handlers->add('sample', function(ShortcodeInterface $s) {    
   return (new JsonSerializer())->serialize($s);
   });
$processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);

$text = 'x [sample arg=val]cnt[/sample] y';
$result = 'x {"name":"sample","args":{"arg":"val"},"content":"cnt"} y';
assert($result === $processor->process($text);
```

Default handler can be set to catch any unsupported shortcodes:

```php
$handlers = new HandlerContainer();
$handlers->setDefault(function(ShortcodeInterface $s) {
    return sprintf('[Invalid shortcode %s!]', $s->getName());
    });
$processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);

$text = 'something [x arg=val]content[/x] other';
$result = 'something [Invalid shortcode x!] other';
assert($result === $processor->process($text);
```

Shortcodes can be aliased to reuse same handler:

```php
$handlers = new HandlerContainer();
$handlers->add('sample', function(ShortcodeInterface $s) {
   return (new JsonSerializer())->serialize($s);
   });
$handlers->addAlias('spl', 'sample');
$processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);

$text = 'sth [spl arg=val]cnt[/spl] end';
$result = 'sth {"name":"spl","parameters":{"arg":"val"},"content":"cnt"} end';
assert($result === $processor->process($text));
```

Recursive shortcode processing is enabled by default with unlimited levels, use `Processor::withRecursionDepth($depth)` to control that behavior:

```php
$handlers = (new HandlerContainer())
    ->addHandler('c', function(Shortcode $s) { return $s->getContent() })
    ->addHandlerAlias('d', 'c');
$processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);

assert('xyz' === $processor->process('[c]x[d]y[/d]z[/c]'));
assert('x[d]y[/d]z' === $processor->withRecursionDepth(false)->process('[c]x[d]y[/d]z[/c]'))
```

Default number of iterations is `1`, but this can be controlled using `Processor::setMaxIterations()`:

```php
$handlers = new HandlerContainer();
$handlers->add('c', function(Shortcode $s) { return $s->getContent() })
$handlers->addAlias('d', 'c');
$handlers->addAlias('e', 'c');
$processor = new Processor(new RegexExtractor(), new RegexParser(), $handlers);
$processor = $processor->withRecursionDepth(0);

$text = '[c]a[d]b[e]c[/e]d[/d]e[/c]';
assert('a[d]b[e]c[/e]d[/d]e' === $processor->withMaxIterations(1)->process($text));
assert('ab[e]c[/e]de' === $processor->withMaxIterations(2)->process($text));
assert('abcde' === $processor->withMaxIterations(3)->process($text));
assert('abcde' === $processor->withMaxIterations(null)->process($text));
```

**Extraction**

Create instance of class `Extractor` and use its `extract()` method to get array of shortcode matches that contain the matched string and its position:

```php
$extractor = new RegexExtractor();
$matches = $extractor->extract('something [x] other [sth]sth[/sth] other');

var_dump($matches); // array(Match(10, '[x]'), Match(20, '[sth]sth[/sth]'))
```

**Parsing**

Create instance of `Parser` class and use its `parse()` method to parse single shortcode string match into `Shortcode` instance with easy access to its name, parameters, and content (null if none present):

```php
$parser = new RegexParser();
$shortcode = $parser->parse('[code arg=value]something[/code]');

var_dump($shortcode); // Shortcode('code', array('arg' => 'value'), 'something')
```

**Syntax**

Both `Parser` and `Extractor` classes provide configurable shortcode syntax capabilities which can be achieved by passing `Syntax` object as their first argument:

```php
// all of these are equivalent, builder is more verbose
$syntax = new Syntax('[[', ']]', '//', '==', '""');
$syntax = (new SyntaxBuilder())
    ->setOpeningTag('[[')
    ->setClosingTag(']]')
    ->setClosingTagMarker('//')
    ->setParameterValueSeparator('==')
    ->setParameterValueDelimiter('""')
    ->getSyntax();

// create both objects as usual, if nothing is passed defaults are assumed
$parser = new Parser($syntax);
$extractor = new Extractor($syntax);

// will contain one matched shortcode string 
$matches = $extractor->extract('x [[code arg==""value other""]]content[[//code]] y');

// will contain correctly parsed shortcode inside passed string
$shortcode = $parser->parse('[[code arg==""value other""]]content[[//code]]');
```

Different syntaxes can be passed to both objects but that will result in an unpredictable behavior if used for example inside `Processor` class or passing extracted matches into parser manually. Do that only when researching and on your own risk.

## Edge cases

* unsupported shortcodes (no registered handler or default handler) will be ignored and left as they are,
* mismatching closing shortcode (`[code]content[/codex]`) will be ignored, opening tag will be interpreted as self-closing shortcode, eg. `[code /]`,
* overlapping shortcodes (`[code]content[inner][/code]content[/inner]`) are not supported and will be interpreted as self-closing, eg. `[code]content[inner /][/code]`, second closing tag will be ignored,
* nested shortcodes with the same name are also considered overlapping, which means that (assume that shortcode `[c]` returns its content) string `[c]x[c]y[/c]z[/c]` will be interpreted as `xyz[/c]` (first closing tag was matched to first opening tag). This can be solved by aliasing given shortcode handler name, because for example `[c]x[d]y[/d]z[/c]` will be processed correctly.

## Ideas

Looking for contribution ideas? Here you are:

* XML serializer,
* YAML serializer,
* specialized exceptions classes,
* example handlers for common shortcodes (`[b]`, `[i]`, `[url]`),
* specialized parameter values (`array=value,value`, `map=key:value,key:value`),
* events fired at various stages of text processing,
* ...your idea?

## License

See LICENSE file in the main directory of this library.
