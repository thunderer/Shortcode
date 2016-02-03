# Shortcode

[![Build Status](https://travis-ci.org/thunderer/Shortcode.png?branch=master)](https://travis-ci.org/thunderer/Shortcode)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d/mini.png)](https://insight.sensiolabs.com/projects/5235d5e3-d112-48df-bc07-d4555aef293d)
[![License](https://poser.pugx.org/thunderer/shortcode/license.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Latest Stable Version](https://poser.pugx.org/thunderer/shortcode/v/stable.svg)](https://packagist.org/packages/thunderer/shortcode)
[![Total Downloads](https://poser.pugx.org/thunderer/shortcode/downloads)](https://packagist.org/packages/thunderer/shortcode)
[![Dependency Status](https://www.versioneye.com/user/projects/551d5385971f7847ca000002/badge.svg)](https://www.versioneye.com/user/projects/551d5385971f7847ca000002)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/thunderer/Shortcode/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/thunderer/Shortcode/?branch=master)
[![Code Climate](https://codeclimate.com/github/thunderer/Shortcode/badges/gpa.svg)](https://codeclimate.com/github/thunderer/Shortcode)

Shortcode is a framework agnostic PHP library allowing to find, extract and process text fragments called "shortcodes" or "BBCodes". It consists of several parts, each of them containing logic responsible for different stages of processing input:

- parsers that extract shortcodes from text and transform them to fully configured objects,
- serializers that allow to convert shortcodes data from and to different formats like XML, JSON, and YAML,
- processors that take text with shortcodes and allow to replace them using the registered handlers.

Examples of shortcode syntax are shown in the code below:

```
[user-profile /]
[image width=600]
[link href="http://google.pl" color=red]
[quote="Thunderer"]This is a quote.[/quote]
[text color="red"]This is a text.[/text]
```

## Installation

There are no required dependencies and all PHP versions from 5.3 up to latest 7.0 are supported. This library is available on Composer/Packagist as `thunderer/shortcode`, to install it execute:

```
composer require thunderer/shortcode ^0.5
```

or manually update your `composer.json` with:

```
(...)
"require": {
    "thunderer/shortcode": "^0.5"
}
(...)
```

and run `composer install` or `composer update` afterwards. If you're not using Composer, download sources from GitHub and load them as required. But really, please use Composer.

## Usage

### Processing

Shortcodes are processed using `Processor` which requires a parser to extract them from source text and handlers to compute their replacements. The example below shows how to implement a simple handler that greets the person with name passed as an argument:

```php
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

$handlers = new HandlerContainer();
$handlers->add('profile', function(ShortcodeInterface $s) {
    return sprintf('Hello, my name is %s!', $s->getParameter('name'));
});

$processor = new Processor(new RegularParser(), $handlers);
assert('Hello, my name is Thomas!' === $processor->process('[profile name="Thomas"]'));
```

### Configuration

`Processor` has several configuration options available as `with*()` methods which return the new, changed instance to keep the object immutable.

- `withRecursionDepth($depth)` controls the nesting level - how many levels of shortcodes are actually processed. If this limit is reached, all shortcodes deeper than level are ignored. If the `$depth` value is null, nesting level is not checked, if it's zero then nesting is disabled (only topmost shortcodes are processed). Any integer greater than zero sets the nesting level limit,
- `withMaxIterations($iterations)` controls the number of iterations that the source text is processed in. This means that source text is processed internally that number of times until the limit was reached or there are no shortcodes left. If the `$iterations` parameter value is null, there is no iterations limit, any integer greater than zero sets the limit,
- `withAutoProcessContent($flag)` controls automatic processing of shortcode's content before calling its handler. If the `$flag` parameter is `true` then handler receives shortcode with already processed content, if `false` then handler must process nested shortcodes itself (or leave them for the remaining iterations),
- `withEventContainer($events)` registers event container which provides handlers for all the events fired at various stages of processing text. Read more about events in the section dedicated to them.

### Events

If processor was configured with events container there are several possibilities to alter the way shortcodes are processed:

- `Events::FILTER_SHORTCODES` uses `FilterShortcodesEvent` class. It receives current parent shortcode and array of shortcodes from parser. Its purpose is to allow modifying that array before processing them,
- `Events::REPLACE_SHORTCODES` uses `ReplaceShortcodesEvent` class and receives the parent shortcode, currently processed text, and array of replacements. It can alter the way shortcodes handlers results are applied to the source text. If none of the listeners set the result, the default method is used.

The example below shows how to implement a `[raw]` shortcode that returns its verbatim content without calling any handler for nested shortcodes:

```php
use Thunder\Shortcode\Event\FilterShortcodesEvent;
use Thunder\Shortcode\EventContainer\EventContainer;
use Thunder\Shortcode\Events;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

$handlers = new HandlerContainer();
$handlers->add('raw', function(ShortcodeInterface $s) { return $s->getContent(); });
$handlers->add('n', function(ShortcodeInterface $s) { return $s->getName(); });
$handlers->add('c', function(ShortcodeInterface $s) { return $s->getContent(); });

$events = new EventContainer();
$events->addListener(Events::FILTER_SHORTCODES, function(FilterShortcodesEvent $event) {
    $parent = $event->getParent();
    if($parent && ($parent->getName() === 'raw' || $parent->hasAncestor('raw'))) {
        $event->setShortcodes(array());
    }
});

$processor = new Processor(new RegularParser(), $handlers);
$processor = $processor->withEventContainer($events);

assert(' [n /] [c]cnt[/c] ' === $processor->process('[raw] [n /] [c]cnt[/c] [/raw]'));
assert('n true  [n /] ' === $processor->process('[n /] [c]true[/c] [raw] [n /] [/raw]'));
```

## Parsing

This section discusses available shortcode parsers. Regardless of the parser that you will choose, remember that:

- unsupported shortcodes (no registered handler or default handler) will be ignored and left as they are,
- mismatching closing shortcode (`[code]content[/codex]`) will be ignored, opening tag will be interpreted as self-closing shortcode, eg. `[code /]`,
- overlapping shortcodes (`[code]content[inner][/code]content[/inner]`) will be interpreted as self-closing, eg. `[code]content[inner /][/code]`, second closing tag will be ignored,

There are three included parsers in this library:

- `RegularParser` is the most powerful and correct parser available in this library. It contains the actual parser designed to handle all the issues with shortcodes like proper nesting or detecting invalid shortcode syntax. It's slighly slower than regex-based parser described below,
- `RegexParser` uses a regular expression crafted specially to handle shortcode syntax as much as regex engine allows. It is fastest among the parsers included in this library, but it can't handle nesting properly, which means that nested shortcodes with the same name are also considered overlapping, which means that (assume that shortcode `[c]` returns its content) string `[c]x[c]y[/c]z[/c]` will be interpreted as `xyz[/c]` (first closing tag was matched to first opening tag). This can be solved by aliasing given shortcode handler name, because for example `[c]x[d]y[/d]z[/c]` will be processed correctly,
- `WordpressParser` contains code copied from the latest currently available WordPress (4.3.1). It is also a regex-based parser, but the included regular expression is quite weak, it for example won't support BBCode syntax (`[name="param" /]`). This is intentional to keep the compatibility with what WordPress is capable of if you need that compatibility.

### Syntax

All parsers (except `WordpressParser`) support configurable shortcode syntax which can be configured by passing `SyntaxInterface` object as the first parameter. There is a convenience class `CommonSyntax` that contains default syntax. Usage is shown in the examples below:

```php
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Syntax\CommonSyntax;
use Thunder\Shortcode\Syntax\Syntax;
use Thunder\Shortcode\Syntax\SyntaxBuilder;

$builder = new SyntaxBuilder();
```

Default syntax (called "common" in this library):

```
$defaultSyntax = new Syntax(); // without any arguments it defaults to common syntax
$defaultSyntax = new CommonSyntax(); // convenience class
$defaultSyntax = new Syntax('[', ']', '/', '=', '"'); // created explicitly
$defaultSyntax = $builder->getSyntax(); // builder defaults to common syntax
```

Syntax with doubled tokens:

```php
$doubleSyntax = new Syntax('[[', ']]', '//', '==', '""');
$doubleSyntax = $builder // actually using builder
    ->setOpeningTag('[[')
    ->setClosingTag(']]')
    ->setClosingTagMarker('//')
    ->setParameterValueSeparator('==')
    ->setParameterValueDelimiter('""')
    ->getSyntax();
```

Something entirely different just to show the possibilities:

```php
$differentSyntax = new Syntax('@', '#', '!', '&', '~');
```

Verify that each syntax works properly:

```
$handlers = new HandlerContainer();
$handlers->add('up', function(ShortcodeInterface $s) {
    return strtoupper($s->getContent());
});

$defaultRegex = new Processor(new RegexParser($defaultSyntax), $handlers);
$doubleRegex = new Processor(new RegexParser($doubleSyntax), $handlers);
$differentRegular = new Processor(new RegularParser($differentSyntax), $handlers);

assert('a STRING z' === $defaultRegex->process('a [up]string[/up] z'));
assert('a STRING z' === $doubleRegex->process('a [[up]]string[[//up]] z'));
assert('a STRING z' === $differentRegular->process('a @up#string@!up# z'));
```

## Serialization

This library supports several (un)serialization formats - XML, YAML, JSON and Text. Examples below shows how to both serialize and unserialize the same shortcode in each format:

```php
use Thunder\Shortcode\Serializer\JsonSerializer;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Serializer\XmlSerializer;
use Thunder\Shortcode\Serializer\YamlSerializer;
use Thunder\Shortcode\Shortcode\Shortcode;

$shortcode = new Shortcode('quote', array('name' => 'Thomas'), 'This is a quote!');
```

Text:

```php
$text = '[quote name=Thomas]This is a quote![/quote]';
$textSerializer = new TextSerializer();

$serializedText = $textSerializer->serialize($shortcode);
assert($text === $serializedText);
$unserializedFromText = $textSerializer->unserialize($serializedText);
assert($unserializedFromText->getName() === $shortcode->getName());
```

JSON:

```php
$json = '{"name":"quote","parameters":{"name":"Thomas"},"content":"This is a quote!","bbCode":null}';
$jsonSerializer = new JsonSerializer();
$serializedJson = $jsonSerializer->serialize($shortcode);
assert($json === $serializedJson);
$unserializedFromJson = $jsonSerializer->unserialize($serializedJson);
assert($unserializedFromJson->getName() === $shortcode->getName());
```

YAML:

```
$yaml = "name: quote
parameters:
    name: Thomas
content: 'This is a quote!'
bbCode: null
";
$yamlSerializer = new YamlSerializer();
$serializedYaml = $yamlSerializer->serialize($shortcode);
assert($yaml === $serializedYaml);
$unserializedFromYaml = $yamlSerializer->unserialize($serializedYaml);
assert($unserializedFromYaml->getName() === $shortcode->getName());
```

XML:

```
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<shortcode name="quote">
  <bbCode/>
  <parameters>
    <parameter name="name"><![CDATA[Thomas]]></parameter>
  </parameters>
  <content><![CDATA[This is a quote!]]></content>
</shortcode>
';
$xmlSerializer = new XmlSerializer();
$serializedXml = $xmlSerializer->serialize($shortcode);
assert($xml === $serializedXml);
$unserializedFromXml = $xmlSerializer->unserialize($serializedXml);
assert($unserializedFromXml->getName() === $shortcode->getName());
```

## Handlers

To be implemented (PR #33). See the current state on `builtin-handlers` branch.

There are several builtin shortcode handlers available as classes in `Thunder\Shortcode\Handler` namespace:

- `NameHandler` always returns shortcode's name, for example `[pre arg=val]content[/pre]` becomes `pre`,
- `ContentHandler` always returns shortcode's content. It discards its opening and closing tag, for example, `[var]code[/var]` becomes `code`,
- `NullHandler` completely discards shortcode with all nested shortcodes,
- `DeclareHandler` allows to dynamically create shortcode handler with name as first parameter that will also replace all placeholders in text passed as arguments. Example: `[declare user]Your age is %age%.[/declare]` created handler for shortcode `user` and when used like `[user age=18]` the result is `Your age is 18.`,
- `EmailHandler` replaces the email address or shortcode content as clickable `mailto:` link:
  - `[email="email@example.com" /]` becomes `<a href="email@example.com">email@example.com</a>`,
  - `[email]email@example.com[/email]` becomes `<a href="email@example.com">email@example.com</a>`,
  - `[email="email@example.com"]Contact me![/email]` becomes `<a href="email@example.com">Contact me!</a>`,
- `PlaceholderHandler` replaces all placeholders with arguments values. `[text year=1970]News from year %year%.[/text]` becomes `News from year 1970.`,
- `SerializerHandler` replaces shortcode with its serialized value using serializer passed as an argument in class' constructor. If configured with `JsonSerializer`, `[json /]` becomes `{"name":"json", "arguments": [], "content": null, "bbCode": null}`. This could be useful for debugging your shortcodes,
- `UrlHandler` replaces its content with a clickable link:
  - `[url]http://example.com[/url]` becomes `<a href="http://example.com">http://example.com</a>`,
  - `[url="http://example.com"]Visit my site![/url]` becomes `<a href="http://example.com">Visit my site!</a>`,
- `WrapHandler` allows to specify the value that should be placed before and after shortcode content. If configured with `<strong>` and `</strong>`, the text `[b]Bold text.[/b]` becomes `<strong>Bold text.</strong>`.

## Contributing

Want to contribute? Perfect! Submit an issue or Pull Request and explain what would you like to see in this library.

## License

See LICENSE file in the main directory of this library.
