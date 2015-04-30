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
use Thunder\Shortcode\ShortcodeFacade;

$facade = ShortcodeFacade::create(null, array(
    'name' => function(Shortcode $s) { return $s->getName(); },
    'content' => function(Shortcode $s) { return $s->getContent(); },
    ), array(
    'c' => 'content',
    'n' => 'name',
    ));
    
$facade->extract('[c]');
$facade->parse('[c]');
$facade->process('[c]');

$s = new Shortcode('c', array(), null);
$facade->serializeToText($s);
$facade->unserializeFromText('[c]');
$facade->serializeToJson($s);
$facade->unserializeFromJson('{"name":"c","parameters":[],"content":null}');
```

All those calls are equivalent to the examples below. If you want to change the dependencies, extend `ShortcodeFacade` class and replace them by overloading protected `create*` methods.

**Replacement**

Create `Processor` class instance, register required shortcodes handlers and use `process()` method to dynamically replace found matches using registered callbacks:

```php
use Thunder\Shortcode\Extractor;
use Thunder\Shortcode\Parser;
use Thunder\Shortcode\Processor;
use Thunder\Shortcode\Shortcode;
use Thunder\Shortcode\Serializer\JsonSerializer;

$processor = new Processor(new Extractor(), new Parser());
$processor->addHandler('sample', function(Shortcode $s) {    
    return (new JsonSerializer())->serialize($s);
    });
assert('x {"name":"sample","args":{"arg":"val"},"content":"cnt"} y'
    === $processor->process('x [sample arg=val]cnt[/sample] y');
```

Default handler can be set to catch any unsupported shortcodes:

```php
$processor->setDefaultHandler('sample', function(Shortcode $s) {    
    return sprintf('[Invalid shortcode %s!]', $s->getName());
    });
assert('something [Invalid shortcode x!] other' 
    === $processor->process('something [x arg=val]content[/x] other');
```

Shortcodes can be aliased to reuse same handler:

```php
$processor->addHandlerAlias('spl', 'sample');
assert('sth {"name":"spl","parameters":{"arg":"val"},"content":"cnt"} end'
    === $processor->process('sth [spl arg=val]cnt[/spl] end');
```

Recursive shortcode processing is enabled by default, use `Processor::setRecursion($status)` and `Processor::setRecursionDepth($depth)` to control that behavior:

```php
$processor->addHandler('c', function(Shortcode $s) { return $s->getContent() })
$processor->addHandlerAlias('d', 'c');
assert("xyz" === $processor->process('[c]x[d]y[/d]z[/c]'));
$processor->setRecursion(false);
assert('x[d]y[/d]z' === $processor->process('[c]x[d]y[/d]z[/c]'))
```

Default number of iterations is `1`, but this can be controlled using `Processor::setMaxIterations()`:

```php
$processor->addHandler('c', function(Shortcode $s) { return $s->getContent() })
$processor->addHandlerAlias('d', 'c');
$processor->addHandlerAlias('e', 'c');
$processor->setRecursionDepth(0);

$processor->setMaxIterations(1);
assert("ab[d]cd[/d]e" === $processor->process('a[c]b[d]c[/c]d[/d]e'));

$processor->setMaxIterations(2);
assert("ab[e]c[/e]de" === $processor->process('[c]a[d]b[e]c[/e]d[/d]e[/c]'));

$processor->setMaxIterations(null);
assert('abcde' === $processor->process('[c]a[d]b[e]c[/e]d[/d]e[/c]'));
```

**Extraction**

Create instance of class `Extractor` and use its `extract()` method to get array of shortcode matches:

```php
use Thunder\Shortcode\Extractor;

$extractor = new Extractor();
$matches = $extractor->extract('something [x] other [random]sth[/random] other');

// array(Match(10, '[x]'), Match(20, '[random]sth[/random]'))
var_dump($matches);
```

**Parsing**

Create instance of `Parser` class and use its `parse()` method to parse single shortcode string match into `Shortcode` instance with easy access to its name, parameters, and content (null if none present):

```php
use Thunder\Shortcode\Parser;

$parser = new Parser();
$shortcode = $parser->parse('[code arg=value]something[/code]');

// will contain name "code", one argument and "something" as content.
var_dump($shortcode);
```
**Syntax**

Both `Parser` and `Extractor` classes provide configurable shortcode syntax capabilities which can be achieved by passing `Syntax` object as their first argument:

```php
use Thunder\Shortcode\Syntax;
use Thunder\Shortcode\SyntaxBuilder;

// these two are equivalent, builder is more verbose
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
$matches = $extractor->extract('x [[code arg==""value random""]]content[[//code]] y');

// will contain correctly parsed shortcode inside passed string
$shortcode = $parser->parse('[[code arg==""value random""]]content[[//code]]');
```

Different syntaxes can be passed to both objects but that will result in an unpredictable behavior if used for example inside `Processor` class or passing extracted matches into parser manually. Do that only when researching and on your own risk.

## Edge cases

* unsupported shortcodes (no registered handler) will be ignored and left as they are,
* mismatching closing shortcode (`[code]content[/codex]`) will be ignored, opening tag will be interpreted as self-closing shortcode,
* overlapping shortcodes (`[code]content[inner][/code]content[/inner]`) are not supported and will be interpreted as self-closing, second closing tag will be ignored,
* nested shortcodes with the same name are also considered overlapping, which means that (assume that shortcode `[c]` returns its content) string `[c]x[c]y[/c]z[/c]` will be interpreted as `xyz[/c]` (first closing tag was matched to first opening tag). This can be solved by aliasing given shortcode handler name, because for example `[c]x[d]y[/d]z[/c]` will be processed "correctly".

## Ideas

Looking for contribution ideas? Here you are:

* XML serializer,
* YAML serializer,
* specialized exceptions classes,
* library facade for easier usage,
* example handlers for common shortcodes (`[b]`, `[i]`, `[url]`),
* specialized parameter values (`array=value,value`, `map=key:value,key:value`),
* shortcode validators and strict mode,
* ...your idea?

## License

See LICENSE file in the main directory of this library.
