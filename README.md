# Shortcode

[![Build Status](https://travis-ci.org/thunderer/Shortcode.png?branch=master)](https://travis-ci.org/thunderer/Shortcode)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d/mini.png)](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d)
[![License](https://poser.pugx.org/thunderer/shortcode/license.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Latest Stable Version](https://poser.pugx.org/thunderer/shortcode/v/stable.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Dependency Status](https://www.versioneye.com/user/projects/551d5385971f7847ca000002/badge.svg?style=flat)](https://www.versioneye.com/user/projects/551d5385971f7847ca000002)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Climate](https://codeclimate.com/github/thunderer/Shortcode/badges/gpa.svg)](https://codeclimate.com/github/thunderer/Shortcode)

Shortcode is a framework and library agnostic engine for interpreting and replacing "shortcodes" (small script-like text fragments) using dynamic callbacks. It can be used to create dynamic content replacement mechanism that is usable even by non-technical people without much training. Usual syntax of shortcodes is as shown in the examples below:

```
[shortcode]
[shortcode argument="value"]
[shortcode novalue argument=simple other="complex value"]
[shortcode]content[/shortcode]
[shortcode argument="value"]content[/shortcode]
```

All those variants (and many more, see the tests) are supported.

## Requirements

No required dependencies, only PHP >=5.4

> PHP 5.3 is marked as minimal version in Composer, but it won't work because parsing mechanism relies on passing context of $this into closures (calling object methods inside them). This can be fixed, but since PHP 5.3 has already reached its EOL months ago I really advise you to upgrade to latest stable version. There are many performance improvements and new features that are extremely useful.

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

and run `composer install` or `composer update` afterwards. If you're not using Composer, then you can download sources from GitHub and load them as you wish in your project. But really, please use Composer.

## Usage

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
    
// this will produce JSON encoded parsed data of given shortcode, eg.:
// something {"name":"sample","args":{"argument":"value"},"content":"content"} other
echo $processor->process('something [sample argument=value]content[/sample] other');
```

**Extraction**

Create instance of class `Extractor` and use its `extract()` method to get array of shortcode matches:

```php
use Thunder\Shortcode\Extractor;

$extractor = new Extractor();
$matches = $extractor->extract('something [x] other [random]sth[/random] other');

// array will contain two instances of class Match with match offsets and exact 
// strings, like [10, '[x]'] and [20, '[random]sth[/random]']
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
$syntax = (new SyntaxBuilder())
    ->setOpeningTag('[[')
    ->setClosingTag(']]')
    ->setClosingTagMarker('//')
    ->setParameterValueSeparator('==')
    ->setParameterValueDelimiter('""')
    ->getSyntax();
    
$syntax = new Syntax('[[', ']]', '//', '==', '""');

// create both objects as usual, if nothing is passed defaults are assumed
$parser = new Parser($syntax);
$extractor = new Extractor($syntax);

// will contain one matched shortcode string 
$matches = $extractor->extract('something [[code arg==""value random""]]content[[//code]] other');

// will contain correctly parsed shortcode inside passed string
$shortcode = $parser->parse('[[code arg==""value random""]]content[[//code]]');
```

Different syntaxes can be passed to both objects but that will result in an unpredictable behavior if used for example inside `Processor` class or passing extracted matches into parser manually. Do that only when researching and on your own risk.

## Edge cases

* unsupported shortcodes (no registered handler) will be ignored and left as they are,
* mismatching closing shortcode (`[code]content[/codex]`) will be ignored, opening tag will be interpreted as self-closing shortcode,
* overlapping shortcodes (`[code]content[inner][/code]content[/inner]`) are not supported and will be interpreted as self-closing, closing tag will be ignored.

## Ideas

Looking for contribution ideas? Here you are:

* shortcode aliases,
* configurable processor recursion,
* XML serializer,
* YAML serializer,
* specialized exceptions classes,
* library facade for easier usage,
* example handlers for common shortcodes ([b], [i], [url]),
* specialized parameter values (array=value,value, map=key:value,key:value),
* shortcode validators and strict mode.

## License

See LICENSE file in the main directory of this library.
